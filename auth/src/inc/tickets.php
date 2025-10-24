<?php

function process_ticket_purchase(string $user_id, array $booking_data, float $final_price, ?string $coupon_code = null): bool
{
    $db = db();
    $db->beginTransaction();
    
    try {
        $current_balance = get_user_balance($user_id); 
        if ($current_balance < $final_price) {
            throw new Exception("Bakiye yetersiz.");
        }

        $booked_now = get_booked_seats_by_trip_id($booking_data['trip_id']); 
        $collision = array_intersect($booking_data['seats'], $booked_now);
        if (!empty($collision)) {
            throw new Exception("Seçilen koltuklardan bazıları satılmıştır: " . implode(', ', $collision));
        }

        $new_balance = $current_balance - $final_price;
        $sql_balance = "UPDATE User SET balance = :new_balance WHERE id = :user_id";
        $stmt_balance = $db->prepare($sql_balance);
        $stmt_balance->bindValue(':new_balance', $new_balance);
        $stmt_balance->bindValue(':user_id', $user_id);
        if (!$stmt_balance->execute()) {
            throw new Exception("Bakiye güncelleme başarısız.");
        }

        $ticket_id = generate_unique_id(); 
        $trip = get_trip_by_id($booking_data['trip_id']); 
        $sql_ticket = "INSERT INTO Tickets (id, trip_id, user_id, status, total_price, coupon_code) 
                       VALUES (:id, :trip_id, :user_id, 'active', :total_price, :coupon_code)";
        $stmt_ticket = $db->prepare($sql_ticket);
        $stmt_ticket->bindValue(':id', $ticket_id);
        $stmt_ticket->bindValue(':trip_id', $booking_data['trip_id']);
        $stmt_ticket->bindValue(':user_id', $user_id);
        $stmt_ticket->bindValue(':total_price', $final_price);
        $stmt_ticket->bindValue(':coupon_code', $coupon_code, $coupon_code ? PDO::PARAM_STR : PDO::PARAM_NULL);
        
        if (!$stmt_ticket->execute()) {
            throw new Exception("Bilet kaydı oluşturma başarısız.");
        }
        
        
        foreach ($booking_data['seats'] as $seat_number) {
            $sql_seat = "INSERT INTO booked_seats (id, ticket_id, seat_number) 
                         VALUES (:id, :ticket_id, :seat_number)";
            $stmt_seat = $db->prepare($sql_seat);
            $stmt_seat->bindValue(':id', generate_unique_id()); 
            $stmt_seat->bindValue(':ticket_id', $ticket_id);
            $stmt_seat->bindValue(':seat_number', $seat_number);
            
            if (!$stmt_seat->execute()) {
                throw new Exception("Koltuk kaydı oluşturma başarısız: {$seat_number}");
            }
        }
        
        $db->commit();
        return true;

    } catch (Exception $e) {
        $db->rollBack();
        error_log("Bilet alım TRx hatası: " . $e->getMessage());
        flash("Bilet alım işlemi başarısız oldu: " . $e->getMessage(), 'flash_error');
        return false;
    }
}


function get_tickets_by_user_id(string $user_id): array
{
    $sql = "
        SELECT 
            ti.id AS ticket_id,
            ti.total_price,
            ti.status,
            ti.created_at AS purchase_date,
            tr.departure_city,
            tr.destination_city AS arrival_city,
            tr.departure_time,
            bc.name AS company_name,
            (SELECT GROUP_CONCAT(seat_number) FROM booked_seats WHERE ticket_id = ti.id) AS seats
        FROM 
            Tickets ti
        JOIN 
            Trips tr ON ti.trip_id = tr.id
        JOIN 
            Bus_Company bc ON tr.company_id = bc.id
        WHERE 
            ti.user_id = :user_id
        ORDER BY 
            tr.departure_time DESC
    ";

    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Kullanıcı biletleri çekme hatası: " . $e->getMessage());
        return [];
    }
}


function get_tickets_for_reporting(?string $company_id): array
{
    $sql = "
        SELECT 
            ti.id AS ticket_id,
            ti.total_price,
            ti.status,
            u.full_name AS user_name,
            tr.departure_city,
            tr.destination_city AS arrival_city,
            tr.departure_time,
            bc.name AS company_name,
            (SELECT GROUP_CONCAT(seat_number) FROM booked_seats WHERE ticket_id = ti.id) AS seats
        FROM 
            Tickets ti
        JOIN 
            Trips tr ON ti.trip_id = tr.id
        JOIN 
            Bus_Company bc ON tr.company_id = bc.id
        JOIN
            User u ON ti.user_id = u.id
    ";

    if ($company_id) {
        $sql .= " WHERE tr.company_id = :company_id";
    }
    $sql .= " ORDER BY ti.created_at DESC";

    $statement = db()->prepare($sql);
    if ($company_id) $statement->bindValue(':company_id', $company_id);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}


function get_trip_seat_status_for_admin(string $trip_id): array
{
    $sql = "
        SELECT
            bs.seat_number,
            u.full_name AS booked_by_user,
            ti.id AS ticket_id,
            ti.status AS ticket_status,
            ti.total_price AS ticket_price
        FROM
            booked_seats bs
        JOIN
            Tickets ti ON bs.ticket_id = ti.id
        JOIN
            User u ON ti.user_id = u.id
        WHERE
            ti.trip_id = :trip_id
        ORDER BY
            bs.seat_number ASC
    ";

    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':trip_id', $trip_id);
        $statement->execute();
        $booked_seats_data = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Koltuk numaralarını anahtar olarak kullanarak daha kolay erişim sağla
        $result = [];
        foreach ($booked_seats_data as $seat) {
            $result[$seat['seat_number']] = $seat;
        }
        return $result;
    } catch (PDOException $e) {
        error_log("Sefer koltuk durumu çekme hatası (Admin): " . $e->getMessage());
        return [];
    }
}

/**
 * Verilen ID'ye sahip bileti ve ilişkili sefer bilgilerini getirir.
 *
 * @param string $ticket_id Bilet ID'si.
 * @return array|null Bilet bilgileri veya bulunamazsa null.
 */
function get_ticket_by_id(string $ticket_id): ?array
{
    $sql = "
        SELECT 
            ti.*, 
            tr.departure_time 
        FROM 
            Tickets ti 
        JOIN 
            Trips tr ON ti.trip_id = tr.id 
        WHERE 
            ti.id = :ticket_id
    ";
    $statement = db()->prepare($sql);
    $statement->bindValue(':ticket_id', $ticket_id);
    $statement->execute();
    $ticket = $statement->fetch(PDO::FETCH_ASSOC);
    return $ticket ?: null;
}

/**
 * Bir bileti iptal eder, ücreti iade eder ve koltukları boşa çıkarır.
 *
 * @param string $ticket_id İptal edilecek biletin ID'si.
 * @param string $user_id İşlemi yapan kullanıcının ID'si.
 * @return bool Başarılı ise true, değilse false.
 */
function cancel_ticket(string $ticket_id, string $user_id): bool
{
    $db = db();
    $db->beginTransaction();

    try {
        // 1. Bileti ve sefer bilgilerini al
        $ticket = get_ticket_by_id($ticket_id);

        // 2. Kontroller
        if (!$ticket) throw new Exception("Bilet bulunamadı.");
        if ($ticket['user_id'] !== $user_id) throw new Exception("Bu bileti iptal etme yetkiniz yok.");
        if ($ticket['status'] !== 'active') throw new Exception("Bu bilet zaten iptal edilmiş veya aktif değil.");

        // 3. Süre kontrolü: Sefere 1 saatten az kaldıysa iptal edilemez.
        $cancellation_deadline = strtotime($ticket['departure_time']) - 3600; // 1 saat = 3600 saniye
        if (time() > $cancellation_deadline) {
            throw new Exception("Seferin kalkışına 1 saatten az kaldığı için bilet iptal edilemez.");
        }

        // 4. Ücret iadesi
        $refund_amount = (float)$ticket['total_price'];
        $current_balance = get_user_balance($user_id);
        $new_balance = $current_balance + $refund_amount;

        $sql_balance = "UPDATE User SET balance = :new_balance WHERE id = :user_id";
        $stmt_balance = $db->prepare($sql_balance);
        $stmt_balance->bindValue(':new_balance', $new_balance);
        $stmt_balance->bindValue(':user_id', $user_id);
        if (!$stmt_balance->execute()) throw new Exception("Bakiye iadesi başarısız oldu.");

        // 5. Bilet durumunu 'cancelled' olarak güncelle
        $sql_ticket = "UPDATE Tickets SET status = 'cancelled' WHERE id = :ticket_id";
        $stmt_ticket = $db->prepare($sql_ticket);
        $stmt_ticket->bindValue(':ticket_id', $ticket_id);
        if (!$stmt_ticket->execute()) throw new Exception("Bilet durumu güncellenemedi.");

        // 6. Koltukları boşa çıkar
        $sql_seats = "DELETE FROM booked_seats WHERE ticket_id = :ticket_id";
        $stmt_seats = $db->prepare($sql_seats);
        $stmt_seats->bindValue(':ticket_id', $ticket_id);
        if (!$stmt_seats->execute()) throw new Exception("Koltuklar serbest bırakılamadı.");

        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        flash($e->getMessage(), 'flash_error');
        return false;
    }
}

/**
 * PDF oluşturmak için tek bir biletin tüm detaylarını getirir.
 *
 * @param string $ticket_id Bilet ID'si.
 * @return array|null Biletin tüm detayları veya bulunamazsa null.
 */
function get_ticket_details_for_pdf(string $ticket_id): ?array
{
    $sql = "
        SELECT 
            ti.id AS ticket_id,
            ti.total_price,
            ti.status,
            ti.created_at AS purchase_date,
            ti.coupon_code,
            u.full_name AS user_name,
            u.email AS user_email,
            tr.departure_city,
            tr.destination_city AS arrival_city,
            tr.departure_time,
            tr.arrival_time,
            bc.name AS company_name,
            (SELECT GROUP_CONCAT(seat_number) FROM booked_seats WHERE ticket_id = ti.id) AS seats
        FROM 
            Tickets ti
        JOIN 
            User u ON ti.user_id = u.id
        JOIN 
            Trips tr ON ti.trip_id = tr.id
        JOIN 
            Bus_Company bc ON tr.company_id = bc.id
        WHERE 
            ti.id = :ticket_id
    ";
    $statement = db()->prepare($sql);
    $statement->bindValue(':ticket_id', $ticket_id);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
}
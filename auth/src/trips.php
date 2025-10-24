<?php

function search_trips(string $departure, string $arrival, string $date): array
{
    $sql = "
        SELECT 
            t.id, 
            t.departure_time, 
            t.arrival_time, 
            t.price,
            t.departure_city,
            t.destination_city AS arrival_city, -- Sütun adını SQL'de alias ile değiştiriyoruz
            bc.name AS company_name
        FROM 
            Trips t
        INNER JOIN 
            Bus_Company bc ON t.company_id = bc.id
        WHERE 
            t.departure_city = :departure AND 
            t.destination_city = :arrival AND 
            DATE(t.departure_time) = :date
        ORDER BY 
            t.departure_time ASC;
    ";
    
    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':departure', $departure, PDO::PARAM_STR);
        $statement->bindValue(':arrival', $arrival, PDO::PARAM_STR);
        $statement->bindValue(':date', $date, PDO::PARAM_STR);
        $statement->execute();
        
        return $statement->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        
        error_log("Sefer arama hatası: " . $e->getMessage());
        return [];
    }
}


function get_trips_for_management(?string $company_id): array
{
    $sql = "
        SELECT 
            t.id, 
            t.departure_city,
            t.destination_city AS arrival_city, -- Sütun adını SQL'de alias ile değiştiriyoruz
            t.departure_time, 
            t.price, 
            t.capacity,
            t.company_id,
            bc.name AS company_name
        FROM 
            Trips t
        INNER JOIN 
            Bus_Company bc ON t.company_id = bc.id
    ";
    
    $params = [];
    if ($company_id !== null) {
        $sql .= " WHERE t.company_id = :company_id";
        $params[':company_id'] = $company_id;
    }
    $sql .= " ORDER BY t.departure_time DESC";

    try {
        $statement = db()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Yönetim sefer listesi hatası: " . $e->getMessage());
        return [];
    }
}

function get_booked_seats_by_trip_id(string $trip_id): array
{
    $sql = "
        SELECT 
            bs.seat_number 
        FROM 
            booked_seats bs
        INNER JOIN 
            Tickets t ON bs.ticket_id = t.id
        WHERE 
            t.trip_id = :trip_id
    ";

    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':trip_id', $trip_id);
        $statement->execute();
    
        return $statement->fetchAll(PDO::FETCH_COLUMN); 

    } catch (PDOException $e) {
        error_log("Dolu koltuk çekme hatası: " . $e->getMessage());
        return [];
    }
}

/**
 * Yeni sefer ekler (Create) ve SQL hatalarını yakalar.
 */
function create_trip(string $company_id, string $departure_city, string $destination_city, string $departure_time, string $arrival_time, float $price, int $capacity): bool
{

    // generate_unique_id fonksiyonu helpers.php içinde yoksa, geçici olarak PHP'nin kendi fonksiyonunu kullanalım.
    // Bu, ID'den kaynaklı bir sorunu ekarte etmemizi sağlar.
    if (!function_exists('generate_unique_id')) {
        $id = uniqid('trip_', true);
    } else {
        $id = generate_unique_id(); 
    }
    
    $sql = "INSERT INTO Trips (id, company_id, departure_city, destination_city, departure_time, arrival_time, price, capacity) 
            VALUES (:id, :company_id, :departure_city, :destination_city, :departure_time, :arrival_time, :price, :capacity)";
    
    try {
        $statement = db()->prepare($sql);
        
        $statement->bindValue(':id', $id);
        $statement->bindValue(':company_id', $company_id);
        $statement->bindValue(':departure_city', $departure_city);
        $statement->bindValue(':destination_city', $destination_city);
        $statement->bindValue(':departure_time', $departure_time);
        $statement->bindValue(':arrival_time', $arrival_time); 
        // Fiyatı string olarak değil, ondalıklı sayı olarak gönder. Bu, bölgesel ayar sorunlarını çözer.
        $statement->bindValue(':price', $price); 
        $statement->bindValue(':capacity', $capacity, PDO::PARAM_INT);
        
        return $statement->execute();

    } catch (PDOException $e) {
        // Hata günlüğüne daha fazla detay ekleyelim.
        error_log("SEFER KAYIT HATASI: " . $e->getMessage() . " | SQL: " . $sql . " | Data: " . json_encode(func_get_args())); 
        flash("Sefer kaydı başarısız oldu. Lütfen tüm alanları doğru girdiğinizden emin olun.", 'flash_error');
        return false;
    }
}

/**
 * Mevcut bir seferi günceller (Update).
 */
function update_trip(string $id, string $departure_city, string $destination_city, string $departure_time, string $arrival_time, float $price, int $capacity): bool
{
    $sql = "UPDATE Trips 
            SET departure_city = :departure_city, 
                destination_city = :destination_city, 
                departure_time = :departure_time, 
                arrival_time = :arrival_time, 
                price = :price, 
                capacity = :capacity 
            WHERE id = :id";
            
    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->bindValue(':departure_city', $departure_city);
        $statement->bindValue(':destination_city', $destination_city);
        $statement->bindValue(':departure_time', $departure_time);
        $statement->bindValue(':arrival_time', $arrival_time);
        $statement->bindValue(':price', $price);
        $statement->bindValue(':capacity', $capacity, PDO::PARAM_INT);
        
        return $statement->execute();
    } catch (PDOException $e) {
        error_log("SEFER GÜNCELLEME HATASI: " . $e->getMessage());
        flash("Sefer güncelleme başarısız oldu.", 'flash_error');
        return false;
    }
}

/**
 * Verilen ID'ye sahip seferi siler (Delete).
 */
function delete_trip(string $id): bool
{
    $sql = "DELETE FROM Trips WHERE id = :id";
    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        return $statement->execute();
    } catch (PDOException $e) {
        error_log("SEFER SİLME HATASI: " . $e->getMessage());
        return false;
    }
}

/**
 * Verilen ID'ye sahip seferin tüm bilgilerini çeker.
 */
function get_trip_by_id(string $id): ?array
{
    // Sütun adını alias ile değiştirerek kodun geri kalanının çalışmasını sağlıyoruz
    $sql = "SELECT 
                id, company_id, departure_city, 
                destination_city AS arrival_city, 
                departure_time, arrival_time, price, capacity 
            FROM Trips 
            WHERE id = :id";

    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':id', $id);
        $statement->execute();
        $trip = $statement->fetch(PDO::FETCH_ASSOC);
        return $trip ?: null;
    } catch (PDOException $e) {
        error_log("SEFER ÇEKME HATASI (ID: $id): " . $e->getMessage());
        return null;
    }
}

<?php

function get_all_coupons(?string $company_id = null): array
{
    $sql = "
        SELECT 
            c.id, 
            c.code, 
            c.discount, 
            c.usage_limit, 
            c.expire_date,
            bc.name AS company_name
        FROM 
            Coupons c
        LEFT JOIN 
            Bus_Company bc ON c.company_id = bc.id
    ";
    
    $params = [];
    $where = [];

    if ($company_id) {
        $where[] = "(c.company_id = :company_id OR c.company_id IS NULL)";
        $params[':company_id'] = $company_id;
    } 

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY c.expire_date DESC";

    try {
        $statement = db()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Kupon listesi çekme hatası: " . $e->getMessage());
        return [];
    }
}


function create_coupon(string $code, float $discount, int $usage_limit, string $expire_date, ?string $company_id): bool
{
    $sql = "INSERT INTO Coupons (code, discount, usage_limit, expire_date, company_id) 
            VALUES (:code, :discount, :usage_limit, :expire_date, :company_id)";
    
    $statement = db()->prepare($sql);
    $statement->bindValue(':code', $code);
    $statement->bindValue(':discount', $discount);
    $statement->bindValue(':usage_limit', $usage_limit);
    $statement->bindValue(':expire_date', $expire_date);
    $statement->bindValue(':company_id', $company_id, $company_id ? PDO::PARAM_STR : PDO::PARAM_NULL);

    return $statement->execute();
}


function update_coupon(string $id, string $code, float $discount, int $usage_limit, string $expire_date, ?string $company_id): bool
{
    $sql = "UPDATE Coupons SET code = :code, discount = :discount, usage_limit = :usage_limit, expire_date = :expire_date, company_id = :company_id WHERE id = :id";
    
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->bindValue(':code', $code);
    $statement->bindValue(':discount', $discount);
    $statement->bindValue(':usage_limit', $usage_limit);
    $statement->bindValue(':expire_date', $expire_date);
    $statement->bindValue(':company_id', $company_id, $company_id ? PDO::PARAM_STR : PDO::PARAM_NULL);
    
    return $statement->execute();
}


function delete_coupon(string $id): bool
{
    $sql = "DELETE FROM Coupons WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    return $statement->execute();
}


function get_coupon_by_id(string $id): ?array
{
    $sql = "SELECT * FROM Coupons WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->execute();
    return $statement->fetch(PDO::FETCH_ASSOC);
}

/**
 * Verilen kupon kodunu doğrular ve geçerliyse bilgilerini döndürür.
 *
 * @param string $code Kupon kodu.
 * @param string|null $company_id Kuponun geçerli olması gereken firma ID'si.
 * @return array|null Geçerliyse kupon bilgileri, değilse null.
 */
function validate_coupon(string $code, ?string $company_id): ?array
{
    // 1. Kupon koduna göre kuponu bul.
    $sql = "SELECT * FROM Coupons WHERE code = :code";
    $statement = db()->prepare($sql);
    $statement->bindValue(':code', $code);
    $statement->execute();
    $coupon = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$coupon) {
        return null; // Kupon bulunamadı.
    }

    // 2. Son kullanma tarihini kontrol et.
    if (strtotime($coupon['expire_date']) < time()) {
        return null; // Tarihi geçmiş.
    }

    // 3. Kullanım limitini kontrol et (kullanım sayısı takibi eklenirse geliştirilebilir).
    // Şimdilik sadece limitin varlığını kontrol ediyoruz.
    if ($coupon['usage_limit'] <= 0) {
        return null; // Limiti dolmuş.
    }

    // 4. Firma kısıtlamasını kontrol et.
    // Kupon belirli bir firmaya aitse ve seferin firması o değilse, geçersiz.
    if ($coupon['company_id'] !== null && $coupon['company_id'] !== $company_id) {
        return null; // Firma uyumsuz.
    }

    return $coupon; // Kupon geçerli.
}
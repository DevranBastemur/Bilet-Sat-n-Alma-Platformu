<?php

function get_all_companies(): array
{
    $sql = "SELECT id, name, logo_path, created_at FROM Bus_Company ORDER BY name ASC";
    try {
        $statement = db()->query($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Firma listesi çekme hatası: " . $e->getMessage());
        return [];
    }
}


function get_company_by_id(string $id): ?array
{
    $sql = "SELECT id, name, logo_path FROM Bus_Company WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->execute();

    $company = $statement->fetch(PDO::FETCH_ASSOC);
    return $company ?: null;
}


function create_company(string $name, string $logo_path = null): bool
{
    // !!! KRİTİK DÜZELTME: ID'yi helpers.php'den gelen fonksiyonla oluşturuyoruz !!!
    $id = generate_unique_id();
    
    $sql = "INSERT INTO Bus_Company (id, name, logo_path) VALUES (:id, :name, :logo_path)";
    $statement = db()->prepare($sql);
    
    $statement->bindValue(':id', $id); // Benzersiz ID'yi bağla
    $statement->bindValue(':name', $name);
    $statement->bindValue(':logo_path', $logo_path, $logo_path ? PDO::PARAM_STR : PDO::PARAM_NULL);

    return $statement->execute();
}


function update_company(string $id, string $name, ?string $logo_path = null): bool
{
    
    $sql = "UPDATE Bus_Company SET name = :name" . ($logo_path ? ", logo_path = :logo_path" : "") . " WHERE id = :id";
    
    $statement = db()->prepare($sql);
    $statement->bindValue(':name', $name);
    $statement->bindValue(':id', $id);
    
    if ($logo_path) {
        $statement->bindValue(':logo_path', $logo_path);
    }
    
    return $statement->execute();
}

function delete_company(string $id): bool
{
    $sql = "DELETE FROM Bus_Company WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    
    return $statement->execute();
}
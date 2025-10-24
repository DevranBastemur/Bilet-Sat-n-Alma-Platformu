<?php

function db(): PDO
{
    static $pdo;

    
    $database_path = realpath(__DIR__ . '/../../config/Otobus_Database.db');

   
    if (!$database_path || !file_exists($database_path)) {
        // Hata mesajındaki yolu, aranan gerçek yolla (config) tutarlı hale getiriyoruz.
        die("HATA: Veritabanı dosyası bulunamadı! Beklenen yol: " . realpath(__DIR__ . '/../../config/') . '/Otobus_Database.db');
    }
    
    if (!$pdo) {
        try {
            $pdo = new PDO("sqlite:" . $database_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Veritabanı bağlantı hatası: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
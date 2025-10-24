
DB_FILE="/var/www/html/config/Otobus_Database.db"
SCHEMA_FILE="/var/www/html/config/schema.sql"

if [ ! -f "$DB_FILE" ]; then
    echo "Veritabanı bulunamadı, '$DB_FILE' oluşturuluyor..."
    sqlite3 "$DB_FILE" < "$SCHEMA_FILE"
    chown www-data:www-data "$DB_FILE"
    chmod 664 "$DB_FILE"
    echo "Veritabanı başarıyla oluşturuldu."
else
    echo "Veritabanı zaten mevcut."
fi

apache2-foreground

<?php

function register_user(string $email, string $username, string $password): bool 
{
   
    $sql = 'INSERT INTO User(full_name, email, password, role)
            VALUES(:full_name, :email, :password, :role)';

    $statement = db()->prepare($sql);

    
    $statement->bindValue(':full_name', $username, PDO::PARAM_STR);
    
    $statement->bindValue(':email', $email, PDO::PARAM_STR);
    $statement->bindValue(':password', password_hash($password, PASSWORD_BCRYPT), PDO::PARAM_STR);
    
    $statement->bindValue(':role', 'kullanici', PDO::PARAM_STR);


    return $statement->execute();
}



function log_user_in(array $user)
{
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['role'] = $user['role'] ?? 'ziyaretci'; 
}


function find_user_by_username_or_email(string $usernameOrEmail)
{
    
    $field = filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL) ? 'email' : 'full_name';
    
    $sql = "SELECT id, full_name, password, role FROM User WHERE {$field} = :value";

    $statement = db()->prepare($sql);
    $statement->bindValue(':value', $usernameOrEmail);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}


function login(string $usernameOrEmail, string $password): bool
{
    
    $user = find_user_by_username_or_email($usernameOrEmail);

    
    if ($user && password_verify($password, $user['password'])) {
        
        log_user_in($user);
        return true;
    }

    
    return false;
}



function is_user_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}


function logout()
{
    if (is_user_logged_in()) {
       
        unset($_SESSION['user_id'], $_SESSION['full_name']);
        
        
        session_destroy();
    }
    
    redirect_to('login.php');
}



function current_user(): string
{

    return $_SESSION['full_name'] ?? ''; 
}


function require_login()
{
    
    if (!is_user_logged_in()) {
        redirect_to('login.php');
    }
}


function current_user_role(): string
{
    
    return $_SESSION['role'] ?? 'ziyaretci';
}


function is_user_in_role(string $role): bool
{

    if (!is_user_logged_in()) {
        return $role === 'ziyaretci';
    }

    return current_user_role() === $role;
}


function has_minimum_role(string $min_role): bool
{
    $roles_hierarchy = [
        'ziyaretci' => 0,
        'kullanici' => 1,
        'firma_admin' => 2,
        'admin' => 3
    ];

    $current_role_value = $roles_hierarchy[current_user_role()] ?? 0;
    $min_role_value = $roles_hierarchy[$min_role] ?? -1; 

    return $current_role_value >= $min_role_value;
}


function require_role(string $required_role)
{
    if (!has_minimum_role($required_role)) {
        flash("Bu sayfaya erişim yetkiniz yok. (Gereken Min. Rol: {$required_role})", 'flash_error');
        redirect_to('index.php'); 
    }
}


function get_user_company_info(string $user_id): ?array
{

    $sql = "
        SELECT 
            u.company_id, 
            bc.name 
        FROM 
            User u
        LEFT JOIN 
            Bus_Company bc ON u.company_id = bc.id
        WHERE 
            u.id = :user_id 
    ";

    $statement = db()->prepare($sql);
    $statement->bindValue(':user_id', $user_id);
    $statement->execute();

    return $statement->fetch(PDO::FETCH_ASSOC);
}

function get_user_balance(string $user_id): float
{
    $sql = "SELECT balance FROM User WHERE id = :user_id";
    
    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
    
        return (float)($result['balance'] ?? 0.0);
        
    } catch (PDOException $e) {
        error_log("Bakiye çekme hatası: " . $e->getMessage());
        return 0.0;
    }
}

function get_all_users_with_company(): array
{
    $sql = "
        SELECT 
            u.id, 
            u.full_name, 
            u.email, 
            u.role, 
            u.balance,
            bc.name AS company_name
        FROM 
            User u
        LEFT JOIN 
            Bus_Company bc ON u.company_id = bc.id
        ORDER BY 
            u.role DESC, u.full_name ASC
    ";
    
    try {
        $statement = db()->query($sql);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Kullanıcı listesi çekme hatası: " . $e->getMessage());
        return [];
    }
}


function get_user_by_id(string $id): ?array
{
    $sql = "SELECT id, full_name, email, role, company_id, balance FROM User WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->execute();
    $user = $statement->fetch(PDO::FETCH_ASSOC);
    return $user ?: null;
}

function create_user(string $full_name, string $email, string $password, string $role, ?string $company_id): bool
{
    $sql = 'INSERT INTO User(full_name, email, password, role, company_id)
            VALUES(:full_name, :email, :password, :role, :company_id)';

    $statement = db()->prepare($sql);

    $statement->bindValue(':full_name', $full_name);
    $statement->bindValue(':email', $email);
    $statement->bindValue(':password', password_hash($password, PASSWORD_BCRYPT));
    $statement->bindValue(':role', $role);
    
    if ($company_id === null || $company_id === 'global' || $company_id === '') {
        $statement->bindValue(':company_id', null, PDO::PARAM_NULL);
    } else {
        $statement->bindValue(':company_id', $company_id, PDO::PARAM_STR);
    }

    return $statement->execute();
}

function delete_user(string $id): bool
{
    $sql = "DELETE FROM User WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    return $statement->execute();
}

function update_user_role_and_company(string $id, string $role, ?string $company_id): bool
{
    $sql = "UPDATE User SET role = :role, company_id = :company_id WHERE id = :id";
    $statement = db()->prepare($sql);
    $statement->bindValue(':id', $id);
    $statement->bindValue(':role', $role);
    
    if ($company_id === null || $company_id === 'global' || $company_id === '') {
        $statement->bindValue(':company_id', null, PDO::PARAM_NULL);
    } else {
        $statement->bindValue(':company_id', $company_id, PDO::PARAM_STR);
    }
    
    return $statement->execute();
}

function is_admin(): bool
{
    return is_user_in_role('admin');
}

function is_company_admin(): bool
{
    return is_user_in_role('firma_admin');
}

function get_user_company_id(string $user_id): ?string
{
    $sql = "SELECT company_id FROM User WHERE id = :user_id";
    
    try {
        $statement = db()->prepare($sql);
        $statement->bindValue(':user_id', $user_id);
        $statement->execute();
        
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        
        return $result['company_id'] ?? null;
        
    } catch (PDOException $e) {
        error_log("Kullanıcı Firma ID'si çekme hatası: " . $e->getMessage());
        return null;
    }
}

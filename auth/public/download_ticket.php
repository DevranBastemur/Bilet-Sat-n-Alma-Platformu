<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../src/bootstrap.php';

if (!is_user_logged_in()) {
    redirect_to(BASE_URL . '/login.php');
}

$ticket_id = $_GET['ticket_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$ticket_id) {
    flash('Geçersiz bilet ID\'si.', 'flash_error');
    redirect_to(BASE_URL . '/my_tickets.php');
}

$ticket = get_ticket_details_for_pdf($ticket_id);

if (!$ticket || ($ticket['user_id'] !== $user_id && !is_admin())) {
    flash('Bu bileti görüntüleme yetkiniz yok.', 'flash_error');
    redirect_to(BASE_URL . '/my_tickets.php');
}

function tr_encode($text) {
    // FPDF'in anlayacağı Latin-5 (Turkish) karakter setine dönüştür
    return iconv('UTF-8', 'ISO-8859-9//TRANSLIT', $text);
}

class PDF extends FPDF
{
    function Header()
    {
        $this->SetFont('Arial','B',15);
        $this->Cell(0, 10, tr_encode('Elektronik Bilet'), 0, 1, 'C');
        $this->Ln(20);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0, 10, tr_encode('Sayfa ') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, tr_encode($ticket['company_name']), 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Bilet Numarası (PNR):'));
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, htmlspecialchars($ticket['ticket_id']), 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Satın Alım Tarihi:'));
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, date('d.m.Y H:i', strtotime($ticket['purchase_date'])), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, tr_encode('Yolcu Bilgileri'), 0, 1);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Ad Soyad:'));
$pdf->Cell(0, 7, tr_encode($ticket['user_name']), 0, 1);
$pdf->Cell(40, 7, tr_encode('E-posta:'));
$pdf->Cell(0, 7, htmlspecialchars($ticket['user_email']), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, tr_encode('Sefer Bilgileri'), 0, 1);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Güzergah:'));
$pdf->Cell(0, 7, tr_encode($ticket['departure_city'] . ' -> ' . $ticket['arrival_city']), 0, 1);
$pdf->Cell(40, 7, tr_encode('Kalkış:'));
$pdf->Cell(0, 7, date('d.m.Y H:i', strtotime($ticket['departure_time'])), 0, 1);
$pdf->Cell(40, 7, tr_encode('Tahmini Varış:'));
$pdf->Cell(0, 7, date('d.m.Y H:i', strtotime($ticket['arrival_time'])), 0, 1);
$pdf->Cell(40, 7, tr_encode('Koltuklar:'));
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, htmlspecialchars($ticket['seats']), 0, 1);
$pdf->Ln(10);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, tr_encode('Bilet Durumu ve Ücret'), 0, 1);
$pdf->Line($pdf->GetX(), $pdf->GetY(), $pdf->GetX() + 190, $pdf->GetY());
$pdf->Ln(2);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Durum:'));
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, $ticket['status'] === 'active' ? tr_encode('Aktif') : tr_encode('İptal Edildi'), 0, 1);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(40, 7, tr_encode('Ödenen Tutar:'));
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 7, number_format($ticket['total_price'], 2, ',', '.') . ' TL', 0, 1);


$file_path = __DIR__ . '/temp_tickets/bilet_' . $ticket_id . '.pdf';

if (!is_dir(__DIR__ . '/temp_tickets')) {
    mkdir(__DIR__ . '/temp_tickets', 0777, true);
}

$pdf->Output('F', $file_path); 

if (file_exists($file_path)) {
    flash('PDF başarıyla sunucuya kaydedildi: ' . basename($file_path), 'flash_success');
} else {
    flash('PDF oluşturma veya kaydetme başarısız oldu.', 'flash_error');
}
redirect_to(BASE_URL . '/my_tickets.php');
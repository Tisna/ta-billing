<?php 
require_once('../connection.php');
require_once('../lib/tcpdf/tcpdf.php');
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$kode = $_GET['kode'];
$id_customer = $_GET['id_customer'];

$query = "SELECT tb_transaksi.id, tb_transaksi.kode, tb_transaksi.tgl, tb_transaksi.pengerjaan, tb_transaksi.uang_muka, tb_transaksi.total_transaksi, tb_pengerjaan_transaksi.waktu, tb_pengerjaan_transaksi.status, tb_customer.nama, tb_customer.tlpn, tb_customer.alamat, tb_admin.username
	FROM tb_transaksi
	LEFT JOIN tb_pengerjaan_transaksi ON tb_transaksi.id = tb_pengerjaan_transaksi.id_transaksi
	LEFT JOIN tb_customer ON tb_transaksi.id_customer = tb_customer.id
	LEFT JOIN tb_admin ON tb_pengerjaan_transaksi.id_admin = tb_admin.id
	WHERE tb_transaksi.id_customer = '{$id_customer}' AND tb_transaksi.kode = '{$kode}' AND tb_pengerjaan_transaksi.status = 2 ";
$statement = $connect->prepare($query);
$statement->execute();	
$results = $statement->fetchAll();	

$customer = $connect->prepare("SELECT * FROM tb_customer WHERE id = $id_customer ");
$customer->execute();
$result_customer = $customer->fetch();

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Billing System');
$pdf->SetTitle('Billing System - Invoice '.$kode);
$pdf->SetSubject('Billing System');
$pdf->SetKeywords('Billing System');

// set default header data
// $pdf->SetHeaderData(PDF_HEADER_TITLE.' '.$kode, PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);


// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}

// ---------------------------------------------------------

// set default font subsetting mode
$pdf->setFontSubsetting(true);
// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set font
// dejavusans is a UTF-8 Unicode font, if you only need to
// print standard ASCII chars, you can use core fonts like
// helvetica or times to reduce file size.
$pdf->SetFont('microsoftyahei', '', 14, '', true);

// Add a page
// This method has several options, check the source code documentation for more information.
$pdf->AddPage();


// Set some content to print
$html = '
	<span>Kepada </span><br>
	<span>Yth. '.$result_customer['nama'].'</span><br>
	<span>'.$result_customer['alamat'].'</span><br>
	<span>Kode : '.$kode.'</span><br>
	<div margin-bottom:40px;></div>
';
// Print text using writeHTMLCell()
// $pdf->writeHTML($html, true, 0, true, 0);
$html .= '<table border="1" style="border-collapse: collapse;margin-top:40px;">';
$html .= '<thead>
	<tr>
		<th align="center">Pengerjaan</th>
		<th align="center">Uang Muka</th>
		<th align="center">Tagihan</th>
	</tr>
</thead>
<tbody>';
$grandTotal = 0;
foreach ($results  as $key => $value) {
	$total = (double) $value["total_transaksi"] - (double) $value["uang_muka"];
	$grandTotal += $total;
	$html .= '
		<tr>
			<td>'.$value["pengerjaan"].'</td>
			<td align="right">'.number_format($value["uang_muka"]).'</td>
			<td align="right">'.number_format($total).'</td>
		</tr>
	';
}
$html .= '
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2">Total</td>
			<td align="right">'.number_format($grandTotal).'</td>
		</tr>
	</tfoot>
</table>';
// Print text using writeHTMLCell()
$pdf->writeHTML($html, true, 0, true, 0);

// ---------------------------------------------------------

// Close and output PDF document
// This method has several options, check the source code documentation for more information.
$pdf->Output("billing_".$kode.".pdf", 'I');

//============================================================+
// END OF FILE
//============================================================+
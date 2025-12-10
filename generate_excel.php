<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

require 'vendor/autoload.php';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Requisitos');

// Headers - Solo ID, Categoría, Estado, Prioridad, % Completado
$headers = ['ID', 'Categoría', 'Estado', 'Prioridad', '% Completado'];
$sheet->fromArray($headers, null, 'A1');

// Aplicar estilos a encabezados
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'border' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];

for ($col = 'A'; $col <= 'E'; $col++) {
    $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
}

// Data - Solo ID, Categoría, Estado, Prioridad, % Completado
$data = [
    // Autenticación (6 requisitos)
    ['ID-001', 'Autenticación', 'Completado', 'Crítica', '100%'],
    ['ID-002', 'Autenticación', 'Completado', 'Crítica', '100%'],
    ['ID-003', 'Autenticación', 'Completado', 'Crítica', '100%'],
    ['ID-004', 'Autenticación', 'Completado', 'Crítica', '100%'],
    ['ID-005', 'Seguridad', 'Completado', 'Crítica', '100%'],
    ['ID-006', 'Seguridad', 'Completado', 'Alta', '100%'],
    
    // Gestión de Instructores (6 requisitos)
    ['ID-007', 'Gestión', 'Completado', 'Crítica', '100%'],
    ['ID-008', 'Gestión', 'Completado', 'Crítica', '100%'],
    ['ID-009', 'Gestión', 'Completado', 'Crítica', '100%'],
    ['ID-010', 'Gestión', 'Completado', 'Alta', '100%'],
    ['ID-011', 'Gestión', 'Completado', 'Media', '100%'],
    ['ID-012', 'Gestión', 'Completado', 'Alta', '100%'],
    
    // Control de Asistencia (7 requisitos)
    ['ID-013', 'Asistencia', 'Completado', 'Crítica', '100%'],
    ['ID-014', 'Asistencia', 'Completado', 'Crítica', '100%'],
    ['ID-015', 'Asistencia', 'Completado', 'Alta', '100%'],
    ['ID-016', 'Asistencia', 'Completado', 'Alta', '100%'],
    ['ID-017', 'Asistencia', 'Completado', 'Media', '100%'],
    ['ID-018', 'Asistencia', 'Completado', 'Alta', '100%'],
    ['ID-019', 'Asistencia', 'Completado', 'Crítica', '100%'],
    
    // Gestión de Turnos (4 requisitos)
    ['ID-020', 'Turnos', 'Completado', 'Alta', '100%'],
    ['ID-021', 'Turnos', 'Completado', 'Alta', '100%'],
    ['ID-022', 'Turnos', 'Completado', 'Media', '100%'],
    ['ID-023', 'Turnos', 'Completado', 'Alta', '100%'],
    
    // Panel de Administración (6 requisitos)
    ['ID-024', 'Admin', 'Completado', 'Crítica', '100%'],
    ['ID-025', 'Admin', 'Completado', 'Alta', '100%'],
    ['ID-026', 'Admin', 'Completado', 'Crítica', '100%'],
    ['ID-027', 'Admin', 'Completado', 'Alta', '100%'],
    ['ID-028', 'Admin', 'Completado', 'Alta', '100%'],
    ['ID-029', 'Admin', 'Completado', 'Media', '100%'],
    
    // Auditoría (8 requisitos)
    ['ID-030', 'Auditoría', 'Completado', 'Crítica', '100%'],
    ['ID-031', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-032', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-033', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-034', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-035', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-036', 'Auditoría', 'Completado', 'Alta', '100%'],
    ['ID-037', 'Auditoría', 'Completado', 'Media', '100%'],
    
    // Seguridad (8 requisitos)
    ['ID-038', 'Seguridad', 'Completado', 'Alta', '100%'],
    ['ID-039', 'Seguridad', 'Completado', 'Media', '100%'],
    ['ID-040', 'Seguridad', 'Completado', 'Alta', '100%'],
    ['ID-041', 'Seguridad', 'Completado', 'Crítica', '100%'],
    ['ID-042', 'Seguridad', 'Completado', 'Crítica', '100%'],
    ['ID-043', 'Seguridad', 'Completado', 'Crítica', '100%'],
    ['ID-044', 'Seguridad', 'Completado', 'Alta', '100%'],
    ['ID-045', 'Seguridad', 'Completado', 'Media', '100%'],
    
    // UI/UX (5 requisitos)
    ['ID-046', 'UI/UX', 'Completado', 'Media', '100%'],
    ['ID-047', 'UI/UX', 'Completado', 'Media', '100%'],
    ['ID-048', 'UI/UX', 'En Desarrollo', 'Alta', '80%'],
    ['ID-049', 'UI/UX', 'Completado', 'Media', '100%'],
    ['ID-050', 'UI/UX', 'Completado', 'Media', '100%'],
    
    // Tecnología (6 requisitos)
    ['ID-051', 'Tecnología', 'Completado', 'Crítica', '100%'],
    ['ID-052', 'Tecnología', 'Completado', 'Crítica', '100%'],
    ['ID-053', 'Tecnología', 'Completado', 'Crítica', '100%'],
    ['ID-054', 'Tecnología', 'Completado', 'Media', '100%'],
    ['ID-055', 'Tecnología', 'Completado', 'Media', '100%'],
    ['ID-056', 'Tecnología', 'Completado', 'Alta', '100%'],
    
    // Notificaciones (3 requisitos)
    ['ID-057', 'Notificaciones', 'Completado', 'Media', '100%'],
    ['ID-058', 'Notificaciones', 'Completado', 'Media', '100%'],
    ['ID-059', 'Notificaciones', 'Completado', 'Media', '100%'],
    
    // Reportes (5 requisitos)
    ['ID-060', 'Reportes', 'Completado', 'Alta', '100%'],
    ['ID-061', 'Reportes', 'Completado', 'Alta', '100%'],
    ['ID-062', 'Reportes', 'Completado', 'Media', '100%'],
    ['ID-063', 'Reportes', 'Completado', 'Media', '100%'],
    ['ID-064', 'Reportes', 'Completado', 'Media', '100%'],
];

// Agregar datos a la hoja
foreach ($data as $index => $row) {
    $sheet->fromArray($row, null, 'A' . ($index + 2));
    
    // Colorear según estado
    $estadoColor = match($row[2]) {
        'Completado' => 'C6EFCE',
        'En Desarrollo' => 'FFC7CE',
        'Pendiente' => 'FFEB9C',
        default => 'FFFFFF'
    };
    
    $rowStyle = [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $estadoColor]],
        'border' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];
    
    $sheet->getStyle('A' . ($index + 2) . ':E' . ($index + 2))->applyFromArray($rowStyle);
}

// Ajustar columnas
$sheet->getColumnDimension('A')->setWidth(10);
$sheet->getColumnDimension('B')->setWidth(18);
$sheet->getColumnDimension('C')->setWidth(14);
$sheet->getColumnDimension('D')->setWidth(11);
$sheet->getColumnDimension('E')->setWidth(14);

// Congelar la primera fila
$sheet->freezePane('A2');

// Crear segunda hoja con resumen
$summary = $spreadsheet->createSheet();
$summary->setTitle('Resumen');

$summary->fromArray(['RESUMEN DEL PROYECTO PLvL+RcT'], null, 'A1');
$summary->getStyle('A1')->applyFromArray(['font' => ['bold' => true, 'size' => 14], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]]);

$summaryData = [
    ['', ''],
    ['Total de Requisitos:', count($data)],
    ['Requisitos Completados:', 63],
    ['Requisitos en Desarrollo:', 1],
    ['Porcentaje de Completación:', '98.4%'],
    ['', ''],
    ['REQUISITOS POR CATEGORÍA', 'Cantidad'],
    ['Autenticación', 4],
    ['Seguridad (adicional)', 2],
    ['Gestión de Instructores', 6],
    ['Control de Asistencia', 7],
    ['Gestión de Turnos', 4],
    ['Panel de Administración', 6],
    ['Auditoría', 8],
    ['Seguridad (general)', 8],
    ['UI/UX', 5],
    ['Tecnología', 6],
    ['Notificaciones', 3],
    ['Reportes', 5],
];

$summary->fromArray($summaryData, null, 'A3');

// Ajustar columnas en resumen
$summary->getColumnDimension('A')->setWidth(35);
$summary->getColumnDimension('B')->setWidth(20);

// Guardar archivo
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save('public/REQUISITOS_PLvL+RcT.xlsx');

echo "✅ Archivo Excel actualizado exitosamente!\n";
echo "📊 Total de requisitos documentados: " . count($data) . "\n";
echo "📈 Porcentaje de completación: 98.4%\n";
echo "📁 Ubicación: public/REQUISITOS_PLvL+RcT.xlsx\n";
echo "📋 Columnas: ID | Categoría | Estado | Prioridad | % Completado\n";

?>

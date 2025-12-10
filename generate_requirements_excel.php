<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class RequirementsExcelGenerator
{
    private $spreadsheet;
    private $writer;
    
    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
    }
    
    public function generate()
    {
        // Crear hojas
        $this->createCoverSheet();
        $this->createFunctionalRequirementsSheet();
        $this->createNonFunctionalRequirementsSheet();
        $this->createModulesSheet();
        $this->createTestingSheet();
        
        // Guardar archivo
        $this->writer = new Xlsx($this->spreadsheet);
        $outputPath = 'REQUISITOS_PLvL+RcT_COMPLETO.xlsx';
        $this->writer->save($outputPath);
        
        echo "✅ Archivo generado exitosamente: {$outputPath}\n";
    }
    
    private function createCoverSheet()
    {
        $sheet = $this->spreadsheet->getActiveSheet();
        $sheet->setTitle('Portada');
        
        $sheet->setCellValue('A1', 'SISTEMA DE GESTIÓN DE ASISTENCIA DE INSTRUCTORES');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setSize(18)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $sheet->setCellValue('A3', 'PLvL + RcT - Plataforma de Lectura de Código de Barras + Control de Turnos');
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A3')->getFont()->setSize(14)->setItalic(true);
        
        $sheet->setCellValue('A5', 'Documento de Requisitos del Sistema');
        $sheet->mergeCells('A5:E5');
        $sheet->getStyle('A5')->getFont()->setSize(12)->setBold(true);
        
        $sheet->setCellValue('A7', 'Versión: 1.0');
        $sheet->setCellValue('A8', 'Fecha de Generación: ' . date('d/m/Y'));
        $sheet->setCellValue('A9', 'Institución: SENA');
        
        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(20);
    }
    
    private function createFunctionalRequirementsSheet()
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Requisitos Funcionales');
        
        $this->setHeaderRow($sheet, ['ID', 'Requisito', 'Descripción', 'Módulo', 'Estado', 'Prioridad']);
        
        $requirements = [
            // Gestión de Instructores
            ['RF001', 'Crear Instructor', 'Registrar nuevo instructor en el sistema con datos personales y horarios', 'Instructores', 'Implementado', 'ALTA'],
            ['RF002', 'Consultar Instructores', 'Listar todos los instructores activos e inactivos del sistema', 'Instructores', 'Implementado', 'ALTA'],
            ['RF003', 'Actualizar Instructor', 'Modificar información personal, horarios y datos de contacto del instructor', 'Instructores', 'Implementado', 'ALTA'],
            ['RF004', 'Eliminar Instructor', 'Marcar instructor como inactivo (eliminación lógica)', 'Instructores', 'Implementado', 'ALTA'],
            ['RF005', 'Generar Código de Barras', 'Asignar código de barras único a cada instructor automáticamente', 'Instructores', 'Implementado', 'ALTA'],
            ['RF006', 'Generar Código Instructor', 'Crear código único INST-XXXX para identificar instructores', 'Instructores', 'Implementado', 'MEDIA'],
            
            // Gestión de Horarios
            ['RF007', 'Configurar Horario Por Defecto', 'Establecer hora entrada (07:00) y salida (16:00) por defecto para instructores', 'Horarios', 'Implementado', 'ALTA'],
            ['RF008', 'Horarios Específicos por Instructor', 'Permitir horarios personalizados diferentes al horario por defecto', 'Horarios', 'Implementado', 'MEDIA'],
            ['RF009', 'Validar Entrada en Horario', 'Verificar si el instructor llega dentro del horario permitido (tolerancia de 15 min)', 'Horarios', 'Implementado', 'ALTA'],
            ['RF010', 'Detectar Llegadas Tardías', 'Registrar y marcar entradas después de 07:15 como tardías', 'Horarios', 'Implementado', 'ALTA'],
            ['RF011', 'Detectar Salidas Anticipadas', 'Identificar salidas antes de la hora programada', 'Horarios', 'Implementado', 'MEDIA'],
            
            // Registro de Asistencia
            ['RF012', 'Registrar Entrada por Código de Barras', 'Capturar entrada del instructor escaneando código de barras', 'Asistencia', 'Implementado', 'ALTA'],
            ['RF013', 'Registrar Salida por Código de Barras', 'Capturar salida del instructor escaneando código de barras', 'Asistencia', 'Implementado', 'ALTA'],
            ['RF014', 'Registrar Asistencia por Cédula', 'Permitir registro de asistencia usando número de cédula si código de barras no funciona', 'Asistencia', 'Implementado', 'MEDIA'],
            ['RF015', 'Timestamp de Registro', 'Registrar fecha y hora exacta del movimiento (entrada/salida)', 'Asistencia', 'Implementado', 'ALTA'],
            ['RF016', 'Asociar Guardia al Registro', 'Vincular cada registro de asistencia con el guardia que lo registró', 'Asistencia', 'Implementado', 'MEDIA'],
            ['RF017', 'Estado del Registro', 'Marcar registros como normal/novedad/ausencia basado en reglas de negocio', 'Asistencia', 'Implementado', 'MEDIA'],
            
            // Detección de Anomalías
            ['RF018', 'Detectar Ausencias', 'Identificar instructores sin registro de entrada en el día', 'Anomalías', 'Implementado', 'ALTA'],
            ['RF019', 'Notificar Anomalías', 'Mostrar alertas para llegadas tardías, salidas anticipadas y ausencias', 'Anomalías', 'Implementado', 'MEDIA'],
            ['RF020', 'Registrar Observaciones', 'Permitir agregar notas sobre situaciones especiales (justificaciones, permiso, etc)', 'Anomalías', 'Implementado', 'MEDIA'],
            
            // Gestión de Vigilantes/Guardias
            ['RF021', 'Crear Vigilante/Guardia', 'Registrar nuevo usuario con rol de guardia para registrar asistencias', 'Usuarios', 'Implementado', 'ALTA'],
            ['RF022', 'Consultar Vigilantes', 'Listar todos los vigilantes/guardias del sistema', 'Usuarios', 'Implementado', 'MEDIA'],
            ['RF023', 'Actualizar Vigilante', 'Modificar datos del guardia (nombre, email, teléfono)', 'Usuarios', 'Implementado', 'MEDIA'],
            ['RF024', 'Eliminar Vigilante', 'Dar de baja un vigilante del sistema', 'Usuarios', 'Implementado', 'MEDIA'],
            
            // Gestión de Turnos
            ['RF025', 'Iniciar Turno', 'Guardia inicia su turno de trabajo registrando la hora de inicio', 'Turnos', 'Implementado', 'ALTA'],
            ['RF026', 'Finalizar Turno', 'Guardia finaliza su turno registrando hora de salida y duración total', 'Turnos', 'Implementado', 'ALTA'],
            
            // Monitoreo y Salud del Sistema
            ['RF027', 'Health Check', 'Endpoint para verificar disponibilidad y salud del sistema', 'Monitoreo', 'Implementado', 'MEDIA'],
            ['RF028', 'Obtener Métricas del Sistema', 'Proporcionar métricas de rendimiento y estadísticas de uso', 'Monitoreo', 'Implementado', 'MEDIA'],
            
            // Reportes y Historial
            ['RF029', 'Historial de Asistencias', 'Visualizar registro completo de entradas/salidas con filtros', 'Reportes', 'Implementado', 'ALTA'],
            ['RF030', 'Generar Reportes', 'Crear reportes con estadísticas de asistencia, tardanzas y ausencias', 'Reportes', 'Implementado', 'ALTA'],
            ['RF031', 'Exportar Reportes', 'Descargar reportes en formato Excel/PDF', 'Reportes', 'Implementado', 'MEDIA'],
            
            // Seguridad
            ['RF032', 'Prevenir Doble Escaneo', 'Validar que no se registren 2 movimientos del mismo tipo en menos de 5 minutos', 'Seguridad', 'Implementado', 'ALTA'],
            ['RF033', 'Ping de Dispositivos', 'Endpoint público para que lectores de código registren su actividad', 'Seguridad', 'Implementado', 'MEDIA'],
            ['RF034', 'Autenticación de Usuarios', 'Sistema de login seguro con email y contraseña', 'Seguridad', 'Implementado', 'ALTA'],
            ['RF035', 'Control de Roles', 'Asignar permisos diferenciados: admin, guardia, vigilante, user', 'Seguridad', 'Implementado', 'ALTA'],
            
            // Autenticación de Dos Factores
            ['RF036', 'Habilitar 2FA por Email', 'Usuario puede activar autenticación de dos factores por correo electrónico', 'Seguridad 2FA', 'Implementado', 'MEDIA'],
            ['RF037', 'Enviar Código 2FA', 'Sistema envía código de verificación al correo del usuario', 'Seguridad 2FA', 'Implementado', 'MEDIA'],
            ['RF038', 'Verificar Código 2FA', 'Usuario ingresa código para completar autenticación de dos factores', 'Seguridad 2FA', 'Implementado', 'MEDIA'],
            ['RF039', 'Códigos de Respaldo', 'Generar códigos de respaldo para recuperación de acceso sin email', 'Seguridad 2FA', 'Implementado', 'MEDIA'],
            ['RF040', 'Deshabilitar 2FA', 'Usuario puede desactivar autenticación de dos factores cuando sea necesario', 'Seguridad 2FA', 'Implementado', 'MEDIA'],
            
            // Personalizacion
            ['RF041', 'Cambiar Tema (Light/Dark)', 'Usuario puede cambiar entre tema claro y oscuro', 'Personalización', 'Implementado', 'BAJA'],
            ['RF042', 'Contraste Alto', 'Opción de activar contraste alto para mejor accesibilidad visual', 'Personalización', 'Implementado', 'BAJA'],
            ['RF043', 'Reducir Animaciones', 'Opción para reducir/deshabilitar animaciones y transiciones', 'Personalización', 'Implementado', 'BAJA'],
            ['RF044', 'Ajustar Tamaño de Fuente', 'Usuario puede cambiar tamaño de fuente (pequeño, normal, grande, extra-grande)', 'Personalización', 'Implementado', 'BAJA'],
            ['RF045', 'Ajustar Espaciado de Líneas', 'Usuario puede modificar espaciado entre líneas de texto', 'Personalización', 'Implementado', 'BAJA'],
            
            // Administración
            ['RF046', 'Configuraciones del Sistema', 'Panel para administrar configuraciones generales del sistema', 'Administración', 'Implementado', 'MEDIA'],
            ['RF047', 'Configurar Horarios de Turnos', 'Definir horarios de jornada (mañana, tarde, noche)', 'Administración', 'Implementado', 'MEDIA'],
            ['RF048', 'Configurar Notificaciones', 'Personalizar duración, posición y sonido de notificaciones', 'Administración', 'Implementado', 'MEDIA'],
            ['RF049', 'Dashboard Administrativo', 'Vista general con estadísticas de usuarios, asistencias y novedades', 'Administración', 'Implementado', 'ALTA'],
            ['RF050', 'Gestión de Respaldos', 'Crear y gestionar copias de seguridad de la base de datos', 'Administración', 'Implementado', 'MEDIA'],
            
            // Auditoría
            ['RF051', 'Registrar Cambios de Seguridad', 'Log de cambios en configuración de autenticación y permisos', 'Auditoría', 'Implementado', 'MEDIA'],
            ['RF052', 'Registrar Cambios de Tema', 'Auditar cambios en preferencias de tema y accesibilidad', 'Auditoría', 'Implementado', 'BAJA'],
            ['RF053', 'Registrar Cambios de Perfil', 'Log de modificaciones en información de usuario y perfil', 'Auditoría', 'Implementado', 'MEDIA'],
            ['RF054', 'Auditoría de Intentos Fallidos 2FA', 'Registrar intentos de verificación 2FA fallidos', 'Auditoría', 'Implementado', 'MEDIA'],
        ];
        
        $row = 2;
        foreach ($requirements as $req) {
            $sheet->setCellValue("A{$row}", $req[0]);
            $sheet->setCellValue("B{$row}", $req[1]);
            $sheet->setCellValue("C{$row}", $req[2]);
            $sheet->setCellValue("D{$row}", $req[3]);
            $sheet->setCellValue("E{$row}", $req[4]);
            $sheet->setCellValue("F{$row}", $req[5]);
            
            // Colorear según prioridad
            $color = match($req[5]) {
                'ALTA' => 'FFD966',
                'MEDIA' => 'B4C7E7',
                'BAJA' => 'C6E0B4',
                default => 'FFFFFF'
            };
            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($color);
            
            $row++;
        }
        
        $this->setColumnWidths($sheet, [12, 35, 45, 20, 15, 12]);
    }
    
    private function createNonFunctionalRequirementsSheet()
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Requisitos No Funcionales');
        
        $this->setHeaderRow($sheet, ['ID', 'Requisito', 'Descripción', 'Categoría', 'Estado']);
        
        $requirements = [
            // Rendimiento
            ['RNF001', 'Tiempo de Respuesta', 'Las operaciones de lectura deben responder en menos de 1 segundo', 'Rendimiento', 'Implementado'],
            ['RNF002', 'Escalabilidad Horizontal', 'Sistema debe soportar múltiples lectores de código de barras simultáneamente', 'Rendimiento', 'Implementado'],
            ['RNF003', 'Caché de Instructores', 'Implementar caché para optimizar búsquedas frecuentes', 'Rendimiento', 'Implementado'],
            
            // Disponibilidad
            ['RNF004', 'Disponibilidad 99%', 'Sistema debe estar disponible el 99% del tiempo operativo', 'Disponibilidad', 'Implementado'],
            ['RNF005', 'Recuperación de Fallos', 'Mecanismo automático para recuperación ante fallos de conexión', 'Disponibilidad', 'Implementado'],
            
            // Seguridad
            ['RNF006', 'Encriptación de Contraseñas', 'Almacenar contraseñas con algoritmo bcrypt', 'Seguridad', 'Implementado'],
            ['RNF007', 'Validación de Entrada', 'Todas las entradas deben ser validadas contra inyección SQL', 'Seguridad', 'Implementado'],
            ['RNF008', 'HTTPS Obligatorio', 'Todas las conexiones deben usar protocolo HTTPS', 'Seguridad', 'Implementado'],
            ['RNF009', 'Autenticación Segura', 'Usar sesiones seguras con tokens CSRF', 'Seguridad', 'Implementado'],
            ['RNF010', 'Rate Limiting', 'Limitar intentos de login y registro para prevenir ataques de fuerza bruta', 'Seguridad', 'Implementado'],
            
            // Usabilidad
            ['RNF011', 'Interfaz Responsiva', 'Diseño adaptable a dispositivos móviles y de escritorio', 'Usabilidad', 'Implementado'],
            ['RNF012', 'Accesibilidad WCAG 2.1', 'Cumplir estándares de accesibilidad para usuarios con discapacidades', 'Usabilidad', 'Implementado'],
            ['RNF013', 'Diseño Intuitivo', 'Interfaz fácil de usar sin requerir capacitación extensa', 'Usabilidad', 'Implementado'],
            ['RNF014', 'Notificaciones Visuales', 'Feedback visual clara para cada acción del usuario', 'Usabilidad', 'Implementado'],
            
            // Mantenibilidad
            ['RNF015', 'Código Modular', 'Código organizado en módulos independientes y reutilizables', 'Mantenibilidad', 'Implementado'],
            ['RNF016', 'Documentación del Código', 'Documentación completa de funciones y clases principales', 'Mantenibilidad', 'Implementado'],
            ['RNF017', 'Logging Detallado', 'Sistema de logs para rastrear errores y eventos importantes', 'Mantenibilidad', 'Implementado'],
            ['RNF018', 'Versionado del Código', 'Control de versiones con Git para seguimiento de cambios', 'Mantenibilidad', 'Implementado'],
            
            // Confiabilidad
            ['RNF019', 'Validación de Datos', 'Garantizar integridad de datos en todas las operaciones', 'Confiabilidad', 'Implementado'],
            ['RNF020', 'Transacciones ACID', 'Usar transacciones para garantizar consistencia de datos', 'Confiabilidad', 'Implementado'],
            ['RNF021', 'Respaldo Automático', 'Realizar copias de seguridad periódicas automaticamente', 'Confiabilidad', 'Implementado'],
            ['RNF022', 'Recuperación de Datos', 'Capacidad de restaurar datos desde respaldos en caso de pérdida', 'Confiabilidad', 'Implementado'],
            
            // Compatibilidad
            ['RNF023', 'Compatibilidad de Navegadores', 'Soportar Chrome, Firefox, Safari y Edge en versiones recientes', 'Compatibilidad', 'Implementado'],
            ['RNF024', 'API REST', 'Proporcionar API REST para integración con sistemas externos', 'Compatibilidad', 'Implementado'],
        ];
        
        $row = 2;
        foreach ($requirements as $req) {
            $sheet->setCellValue("A{$row}", $req[0]);
            $sheet->setCellValue("B{$row}", $req[1]);
            $sheet->setCellValue("C{$row}", $req[2]);
            $sheet->setCellValue("D{$row}", $req[3]);
            $sheet->setCellValue("E{$row}", $req[4]);
            $row++;
        }
        
        $this->setColumnWidths($sheet, [12, 25, 50, 20, 15]);
    }
    
    private function createModulesSheet()
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Módulos del Sistema');
        
        $this->setHeaderRow($sheet, ['Módulo', 'Descripción', 'Componentes', 'Estado', 'Observaciones']);
        
        $modules = [
            ['Gestión de Instructores', 'CRUD completo de instructores con horarios y códigos de barras', 'Modelo, Controller, Vistas, API', 'Completo', 'Operacional'],
            ['Registro de Asistencia', 'Registro de entradas/salidas por código de barras o cédula', 'Modelo, Controller, API, Validaciones', 'Completo', 'Operacional'],
            ['Gestión de Turnos', 'Inicio y cierre de turnos para vigilantes/guardias', 'Modelo, Controller, API', 'Completo', 'Operacional'],
            ['Reportes y Historial', 'Visualización y exportación de reportes de asistencia', 'Controller, Vistas, Exportadores', 'Completo', 'Operacional'],
            ['Seguridad y Autenticación', 'Sistema de login, 2FA y control de acceso por roles', 'Middleware, Controller, Modelos', 'Completo', 'Operacional'],
            ['Personalización', 'Preferencias de tema, accesibilidad y configuración de usuario', 'Controller, Modelos, API', 'Completo', 'Operacional'],
            ['Administración del Sistema', 'Panel administrativo con configuraciones y estadísticas', 'Controller, Vistas, Dashboard', 'Completo', 'Operacional'],
            ['Auditoría y Logs', 'Registro de cambios y eventos del sistema', 'Modelo, Middleware, Controller', 'Completo', 'Operacional'],
            ['Monitoreo', 'Health checks y métricas del sistema', 'Controller, API', 'Completo', 'Operacional'],
        ];
        
        $row = 2;
        foreach ($modules as $mod) {
            $sheet->setCellValue("A{$row}", $mod[0]);
            $sheet->setCellValue("B{$row}", $mod[1]);
            $sheet->setCellValue("C{$row}", $mod[2]);
            $sheet->setCellValue("D{$row}", $mod[3]);
            $sheet->setCellValue("E{$row}", $mod[4]);
            $row++;
        }
        
        $this->setColumnWidths($sheet, [25, 40, 35, 15, 20]);
    }
    
    private function createTestingSheet()
    {
        $sheet = $this->spreadsheet->createSheet();
        $sheet->setTitle('Plan de Pruebas');
        
        $this->setHeaderRow($sheet, ['Caso de Prueba', 'Descripción', 'Módulo', 'Tipo', 'Estado']);
        
        $tests = [
            ['TP001', 'Crear instructor con datos válidos', 'Gestión Instructores', 'Funcional', 'Implementado'],
            ['TP002', 'Crear instructor con datos inválidos', 'Gestión Instructores', 'Validación', 'Implementado'],
            ['TP003', 'Registrar entrada por código de barras', 'Asistencia', 'Funcional', 'Implementado'],
            ['TP004', 'Detectar doble escaneo', 'Seguridad', 'Validación', 'Implementado'],
            ['TP005', 'Registrar llegada tardía', 'Asistencia', 'Funcional', 'Implementado'],
            ['TP006', 'Iniciar y finalizar turno', 'Turnos', 'Funcional', 'Implementado'],
            ['TP007', 'Cambiar tema de usuario', 'Personalización', 'Funcional', 'Implementado'],
            ['TP008', 'Habilitar 2FA', 'Seguridad', 'Funcional', 'Implementado'],
            ['TP009', 'Verificar código 2FA', 'Seguridad', 'Funcional', 'Implementado'],
            ['TP010', 'Generar reportes', 'Reportes', 'Funcional', 'Implementado'],
            ['TP011', 'Exportar reportes a Excel', 'Reportes', 'Funcional', 'Implementado'],
            ['TP012', 'Health check del sistema', 'Monitoreo', 'Funcional', 'Implementado'],
        ];
        
        $row = 2;
        foreach ($tests as $test) {
            $sheet->setCellValue("A{$row}", $test[0]);
            $sheet->setCellValue("B{$row}", $test[1]);
            $sheet->setCellValue("C{$row}", $test[2]);
            $sheet->setCellValue("D{$row}", $test[3]);
            $sheet->setCellValue("E{$row}", $test[4]);
            $row++;
        }
        
        $this->setColumnWidths($sheet, [12, 45, 25, 15, 15]);
    }
    
    private function setHeaderRow($sheet, $headers)
    {
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $sheet->getStyle($col . '1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('203864');
            $col++;
        }
    }
    
    private function setColumnWidths($sheet, $widths)
    {
        $col = 'A';
        foreach ($widths as $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
            $col++;
        }
    }
}

// Ejecutar generador
$generator = new RequirementsExcelGenerator();
$generator->generate();

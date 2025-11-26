<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\EstadoProducto;
use App\Models\Proveedor;

/**
 * Controlador para la gestión de productos
 */
class ProductosController extends Controller
{
    protected $productoModel;
    protected $categoriaModel;
    protected $marcaModel;
    protected $estadoProductoModel;
    protected $proveedorModel;

    public function __construct()
    {
        parent::__construct();
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
        $this->estadoProductoModel = new EstadoProducto();
        $this->proveedorModel = new Proveedor();
    }

    /**
     * Listar todos los productos
     */
    public function index()
    {
        $this->requirePermission('productos');

        $page = (int) $this->get('page', 1);
        $perPage = (int) $this->get('per_page', 10);
        
        // Validar perPage
        $allowedPerPage = [5, 10, 25, 50];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = 10;
        }
        
        $filters = [
            'producto_nombre' => $this->get('producto_nombre'),
            'rela_categoria' => $this->get('rela_categoria'),
            'rela_marca' => $this->get('rela_marca'),
            'rela_estadoproducto' => $this->get('rela_estadoproducto'),
            'precio_min' => $this->get('precio_min'),
            'precio_max' => $this->get('precio_max'),
            'stock_min' => $this->get('stock_min')
        ];

        $result = $this->productoModel->getWithDetails($page, $perPage, $filters);

        // Obtener datos para los filtros
        $categorias = $this->categoriaModel->findAll('categoria_estado = 1', 'categoria_descripcion ASC');
        $marcas = $this->marcaModel->findAll('marca_estado = 1', 'marca_descripcion ASC');
        $estadosProducto = $this->estadoProductoModel->findAll('estadoproducto_estado = 1', 'estadoproducto_descripcion ASC');
        $proveedores = $this->proveedorModel->getProveedoresActivos();

        $data = [
            'title' => 'Gestión de Productos',
            'productos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'estadosProducto' => $estadosProducto,
            'proveedores' => $proveedores,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/productos/listado', $data, 'main');
    }

    /**
     * Mostrar formulario de nuevo producto
     */
    public function create()
    {
        $this->requirePermission('productos');

        if ($this->isPost()) {
            return $this->store();
        }

        $categorias = $this->categoriaModel->findAll('categoria_estado = 1', 'categoria_descripcion ASC');
        $marcas = $this->marcaModel->findAll('marca_estado = 1', 'marca_descripcion ASC');
        $estadosProducto = $this->estadoProductoModel->findAll('estadoproducto_estado = 1', 'estadoproducto_descripcion ASC');

        $data = [
            'title' => 'Nuevo Producto',
            'categorias' => $categorias,
            'marcas' => $marcas,
            'estadosProducto' => $estadosProducto,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/productos/formulario', $data, 'main');
    }

    /**
     * Guardar nuevo producto
     */
    public function store()
    {
        $this->requirePermission('productos');

        if (!$this->isPost()) {
            return $this->redirect('/productos', 'Método no permitido', 'error');
        }

        // Datos del formulario
        $data = [
            'producto_nombre' => trim($this->post('producto_nombre')),
            'producto_descripcion' => trim($this->post('producto_descripcion')),
            'producto_precio' => $this->post('producto_precio'),
            'producto_stock' => $this->post('producto_stock'),
            'rela_categoria' => $this->post('rela_categoria'),
            'rela_marca' => $this->post('rela_marca'),
            'rela_estadoproducto' => $this->post('rela_estadoproducto', 1)
        ];

        // Validar datos
        if (empty($data['producto_nombre'])) {
            return $this->redirect('/productos/create', 'El nombre del producto es requerido', 'error');
        }

        if (empty($data['producto_descripcion'])) {
            return $this->redirect('/productos/create', 'La descripción del producto es requerida', 'error');
        }

        if (!is_numeric($data['producto_precio']) || $data['producto_precio'] <= 0) {
            return $this->redirect('/productos/create', 'El precio debe ser un número positivo', 'error');
        }

        if (!is_numeric($data['producto_stock']) || $data['producto_stock'] < 0) {
            return $this->redirect('/productos/create', 'El stock debe ser un número entero positivo o cero', 'error');
        }

        // Manejar subida de foto
        $producto_foto = null;
        if (isset($_FILES['producto_foto']) && $_FILES['producto_foto']['error'] == 0) {
            $target_dir = "imagenes/productos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["producto_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["producto_foto"]["tmp_name"], $target_file)) {
                $producto_foto = $new_filename;
            }
        }
        
        if ($producto_foto) {
            $data['producto_foto'] = $producto_foto;
        } else {
            $data['producto_foto'] = 'default.jpg';
        }

        if ($this->productoModel->create($data)) {
            return $this->redirect('/productos', 'Producto creado exitosamente', 'success');
        } else {
            return $this->redirect('/productos/create', 'Error al crear el producto', 'error');
        }
    }

    /**
     * Mostrar formulario de edición
     */
    public function edit($id)
    {
        $this->requirePermission('productos');

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        if ($this->isPost()) {
            return $this->update($id);
        }

        $categorias = $this->categoriaModel->findAll('categoria_estado = 1', 'categoria_descripcion ASC');
        $marcas = $this->marcaModel->findAll('marca_estado = 1', 'marca_descripcion ASC');
        $estadosProducto = $this->estadoProductoModel->findAll('estadoproducto_estado = 1', 'estadoproducto_descripcion ASC');

        // Obtener estadísticas del producto
        $estadisticas = $this->productoModel->getProductStatistics($id);

        $data = [
            'title' => 'Editar Producto',
            'producto' => $producto,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'estadosProducto' => $estadosProducto,
            'estadisticas' => $estadisticas,
            'isEdit' => true,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/productos/formulario', $data, 'main');
    }

    /**
     * Actualizar producto existente
     */
    public function update($id)
    {
        $this->requirePermission('productos');

        if (!$this->isPost()) {
            return $this->redirect('/productos', 'Método no permitido', 'error');
        }

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        $data = [
            'producto_nombre' => trim($this->post('producto_nombre')),
            'producto_descripcion' => trim($this->post('producto_descripcion')),
            'producto_precio' => $this->post('producto_precio'),
            'producto_stock' => $this->post('producto_stock'),
            'rela_categoria' => $this->post('rela_categoria'),
            'rela_marca' => $this->post('rela_marca'),
            'rela_estadoproducto' => $this->post('rela_estadoproducto')
        ];

        // Validar datos
        if (empty($data['producto_nombre'])) {
            return $this->redirect("/productos/{$id}/edit", 'El nombre del producto es requerido', 'error');
        }

        if (empty($data['producto_descripcion'])) {
            return $this->redirect("/productos/{$id}/edit", 'La descripción del producto es requerida', 'error');
        }

        if (!is_numeric($data['producto_precio']) || $data['producto_precio'] <= 0) {
            return $this->redirect("/productos/{$id}/edit", 'El precio debe ser un número positivo', 'error');
        }

        if (!is_numeric($data['producto_stock']) || $data['producto_stock'] < 0) {
            return $this->redirect("/productos/{$id}/edit", 'El stock debe ser un número entero positivo o cero', 'error');
        }

        // Manejar subida de foto
        $producto_foto = $producto['producto_foto']; // Mantener foto actual por defecto
        if (isset($_FILES['producto_foto']) && $_FILES['producto_foto']['error'] == 0) {
            $target_dir = "imagenes/productos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["producto_foto"]["name"], PATHINFO_EXTENSION));
            $new_filename = uniqid() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES["producto_foto"]["tmp_name"], $target_file)) {
                // Eliminar foto anterior si existe
                if ($producto['producto_foto'] && file_exists($target_dir . $producto['producto_foto'])) {
                    unlink($target_dir . $producto['producto_foto']);
                }
                $producto_foto = $new_filename;
            }
        }
        
        if ($producto_foto) {
            $data['producto_foto'] = $producto_foto;
        }

        if ($this->productoModel->update($id, $data)) {
            return $this->redirect('/productos', 'Producto actualizado exitosamente', 'success');
        } else {
            return $this->redirect("/productos/{$id}/edit", 'Error al actualizar el producto', 'error');
        }
    }



    /**
     * Ver detalle de producto
     */
    public function show($id)
    {
        $this->requirePermission('productos');

        $producto = $this->productoModel->findWithRelations($id);
        if (!$producto) {
            return $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        // Obtener estadísticas
        $estadisticas = $this->productoModel->getProductStatistics($id);

        $data = [
            'title' => 'Detalle del Producto',
            'producto' => $producto,
            'estadisticas' => $estadisticas,
            'isAdminArea' => true
        ];

        return $this->render('admin/operaciones/productos/detalle', $data, 'main');
    }

    /**
     * Baja lógica
     */
    public function delete($id)
    {
        $this->requirePermission('productos');

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        // Cambiar a estado "baja" (estado 4)
        if ($this->productoModel->changeStatus($id, 4)) {
            return $this->redirect('/productos', 'Producto dado de baja exitosamente', 'success');
        } else {
            return $this->redirect('/productos', 'Error al dar de baja el producto', 'error');
        }
    }

    /**
     * Restaurar producto eliminado
     */
    public function restore($id)
    {
        $this->requirePermission('productos');

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            return $this->redirect('/productos', 'Producto no encontrado', 'error');
        }

        // Cambiar a estado "disponible" (estado 1)
        if ($this->productoModel->changeStatus($id, 1)) {
            return $this->redirect('/productos', 'Producto restaurado exitosamente', 'success');
        } else {
            return $this->redirect('/productos', 'Error al restaurar el producto', 'error');
        }
    }

    /**
     * Cambiar estado mediante AJAX
     */
    public function cambiarEstado($id)
    {
        $this->requirePermission('productos');

        if (!$this->isPost()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $producto = $this->productoModel->find($id);
        if (!$producto) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }

        $nuevoEstado = $this->post('estado');
        $estadosValidos = [1, 2, 3, 4]; // disponible, stock mínimo, sin stock, baja
        
        if (!in_array($nuevoEstado, $estadosValidos)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Estado no válido']);
            exit;
        }

        if ($this->productoModel->changeStatus($id, $nuevoEstado)) {
            $estadoTexto = $this->getEstadoTexto($nuevoEstado);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Estado actualizado correctamente',
                'estado' => $estadoTexto
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
        }
        exit;
    }

    /**
     * Exportar a Excel
     */
    public function exportar()
    {
        $this->requirePermission('productos');

        try {
            $filters = [
                'producto_nombre' => $this->get('producto_nombre'),
                'rela_categoria' => $this->get('rela_categoria'),
                'rela_marca' => $this->get('rela_marca'),
                'rela_estadoproducto' => $this->get('rela_estadoproducto'),
                'precio_min' => $this->get('precio_min'),
                'precio_max' => $this->get('precio_max')
            ];

            $result = $this->productoModel->getAllWithDetailsForExport($filters);
            $productos = $result['data'];

            if (empty($productos)) {
                $this->redirect('/productos', 'No hay datos para exportar', 'error');
                return;
            }

        // Crear archivo Excel
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Encabezados
        $headers = ['ID', 'Nombre', 'Descripción', 'Precio', 'Stock', 'Categoría', 'Marca', 'Estado'];
        $sheet->fromArray($headers, null, 'A1');

        // Datos
        $row = 2;
        foreach ($productos as $producto) {
            $sheet->setCellValue('A' . $row, $producto['id_producto']);
            $sheet->setCellValue('B' . $row, $producto['producto_nombre']);
            $sheet->setCellValue('C' . $row, $producto['producto_descripcion']);
            $sheet->setCellValue('D' . $row, '$' . number_format($producto['producto_precio'], 2));
            $sheet->setCellValue('E' . $row, $producto['producto_stock']);
            $sheet->setCellValue('F' . $row, $producto['categoria_descripcion']);
            $sheet->setCellValue('G' . $row, $producto['marca_descripcion']);
            $sheet->setCellValue('H' . $row, $producto['estadoproducto_descripcion']);
            $row++;
        }

        // Estilos
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
            $filename = 'productos_' . date('Y-m-d_H-i-s') . '.xlsx';
            $filepath = '../temp/' . $filename;
            
            // Crear directorio si no existe
            if (!file_exists('../temp')) {
                mkdir('../temp', 0777, true);
            }
            
            $writer->save($filepath);

            // Descargar archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            readfile($filepath);
            unlink($filepath);
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar productos: " . $e->getMessage());
            $this->redirect('/productos', 'Error al exportar: ' . $e->getMessage(), 'error');
            return;
        }
    }

    /**
     * Exportar a PDF
     */
    public function exportarPdf()
    {
        $this->requirePermission('productos');

        try {
            $filters = [
                'producto_nombre' => $this->get('producto_nombre'),
                'rela_categoria' => $this->get('rela_categoria'),
                'rela_marca' => $this->get('rela_marca'),
                'rela_estadoproducto' => $this->get('rela_estadoproducto'),
                'precio_min' => $this->get('precio_min'),
                'precio_max' => $this->get('precio_max')
            ];

            $result = $this->productoModel->getAllWithDetailsForExport($filters);
            $productos = $result['data'];

            if (empty($productos)) {
                $this->redirect('/productos', 'No hay datos para exportar', 'error');
                return;
            }

        // Crear PDF
        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        $pdf->SetCreator('Sistema de Gestión de Cabañas');
        $pdf->SetAuthor('Sistema');
        $pdf->SetTitle('Listado de Productos');
        
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'Listado de Productos', 0, 1, 'C');
        $pdf->Ln(5);

        // Mostrar filtros aplicados
        $pdf->SetFont('helvetica', '', 10);
        if (array_filter($filters)) {
            $pdf->Cell(0, 5, 'Filtros aplicados:', 0, 1, 'L');
            foreach ($filters as $key => $value) {
                if (!empty($value)) {
                    $label = ucfirst(str_replace('_', ' ', $key));
                    $pdf->Cell(0, 4, "• {$label}: {$value}", 0, 1, 'L');
                }
            }
            $pdf->Ln(3);
        }

        // Tabla
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->Cell(15, 8, 'ID', 1, 0, 'C');
        $pdf->Cell(40, 8, 'Nombre', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Precio', 1, 0, 'C');
        $pdf->Cell(25, 8, 'Stock', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Categoría', 1, 0, 'C');
        $pdf->Cell(35, 8, 'Estado', 1, 1, 'C');

        $pdf->SetFont('helvetica', '', 8);
        foreach ($productos as $producto) {
            $pdf->Cell(15, 6, $producto['id_producto'], 1, 0, 'C');
            $pdf->Cell(40, 6, substr($producto['producto_nombre'], 0, 25), 1, 0, 'L');
            $pdf->Cell(35, 6, '$' . number_format($producto['producto_precio'], 2), 1, 0, 'R');
            $pdf->Cell(25, 6, $producto['producto_stock'], 1, 0, 'C');
            $pdf->Cell(35, 6, substr($producto['categoria_descripcion'], 0, 20), 1, 0, 'L');
            $pdf->Cell(35, 6, substr($producto['estadoproducto_descripcion'], 0, 15), 1, 1, 'L');
        }

        $pdf->Ln(5);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Total de productos: ' . $result['total'], 0, 1, 'L');
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i:s'), 0, 1, 'L');

            $filename = 'productos_' . date('Y-m-d_H-i-s') . '.pdf';
            $pdf->Output($filename, 'D');
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar productos a PDF: " . $e->getMessage());
            $this->redirect('/productos', 'Error al exportar PDF: ' . $e->getMessage(), 'error');
            return;
        }
    }



    /**
     * Exportar plantilla de cotización a Excel
     */
    public function exportarCotizacion()
    {
        $this->requirePermission('productos');

        try {
            // Aplicar los mismos filtros de la consulta actual
            $filters = [
                'producto_nombre' => $this->get('producto_nombre'),
                'rela_categoria' => $this->get('rela_categoria'),
                'rela_marca' => $this->get('rela_marca'),
                'rela_estadoproducto' => $this->get('rela_estadoproducto'),
                'precio_min' => $this->get('precio_min'),
                'precio_max' => $this->get('precio_max'),
                'stock_min' => $this->get('stock_min')
            ];
            
            // Forzar exclusión de productos de baja (estado 4)
            if (empty($filters['rela_estadoproducto'])) {
                $filters['excluir_baja'] = true;
            }
            
            $result = $this->productoModel->getAllWithDetailsForExport($filters);
            $productos = $result['data'];

            if (empty($productos)) {
                $this->redirect('/productos', 'No hay productos disponibles para cotizar', 'error');
                return;
            }

            // Crear archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Título
            $sheet->mergeCells('A1:C1');
            $sheet->setCellValue('A1', 'SOLICITUD DE COTIZACIÓN DE PRODUCTOS');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Información
            $sheet->setCellValue('A2', 'Fecha: ' . date('d/m/Y H:i'));
            $sheet->setCellValue('A3', 'Sistema de Gestión de Cabañas');
            
            // Instrucciones al principio
            $sheet->mergeCells('A5:C5');
            $sheet->setCellValue('A5', 'INSTRUCCIONES:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(10);
            
            $sheet->mergeCells('A6:C6');
            $sheet->setCellValue('A6', '• Complete la columna "Cotización" con el precio unitario de cada producto.');
            $sheet->getStyle('A6')->getFont()->setSize(9);
            
            $sheet->mergeCells('A7:C7');
            $sheet->setCellValue('A7', '• Deje VACÍA la celda de cotización para los productos que NO estén disponibles.');
            $sheet->getStyle('A7')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('FF0000');
            
            $sheet->mergeCells('A8:C8');
            $sheet->setCellValue('A8', '• Una vez completado, devuelva este archivo a nuestro correo de contacto.');
            $sheet->getStyle('A8')->getFont()->setSize(9);
            
            // Encabezados de la tabla (ahora en fila 10)
            $headers = ['Descripción del producto', 'Marca', 'Cotización'];
            $sheet->fromArray($headers, null, 'A10');
            
            // Estilo encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A10:C10')->applyFromArray($headerStyle);

            // Datos de productos (ahora empiezan en fila 11)
            $row = 11;
            foreach ($productos as $producto) {
                $sheet->setCellValue('A' . $row, $producto['producto_nombre']);
                $sheet->setCellValue('B' . $row, $producto['marca_descripcion'] ?? 'Sin marca');
                $sheet->setCellValue('C' . $row, ''); // Columna vacía para que el proveedor complete
                $row++;
            }

            // Ajustar anchos de columna
            $sheet->getColumnDimension('A')->setWidth(50);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);

            // Bordes para la tabla
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            $lastRow = $row - 1;
            $sheet->getStyle('A10:C' . $lastRow)->applyFromArray($styleArray);

            // Fondo amarillo claro para columna Cotización
            $sheet->getStyle('C11:C' . $lastRow)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC']
                ]
            ]);

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            $filename = 'COTIZACION_GENERAL_' . date('Ymd_His') . '.xlsx';
            $filepath = '../temp/' . $filename;
            
            // Crear directorio si no existe
            if (!file_exists('../temp')) {
                mkdir('../temp', 0777, true);
            }
            
            $writer->save($filepath);

            // Descargar archivo
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            
            readfile($filepath);
            unlink($filepath);
            exit;

        } catch (\Exception $e) {
            error_log("Error al exportar plantilla de cotización: " . $e->getMessage());
            $this->redirect('/productos', 'Error al exportar plantilla: ' . $e->getMessage(), 'error');
            return;
        }
    }

    /**
     * Enviar plantilla de cotización por email a proveedor
     */
    public function enviarCotizacion()
    {
        $this->requirePermission('productos');

        if (!$this->isPost()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id_proveedor = (int) $this->post('proveedor_id');
        
        if (!$id_proveedor) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar un proveedor']);
            exit;
        }

        try {
            // Obtener información del proveedor
            $proveedor = $this->proveedorModel->getProveedorCompleto($id_proveedor);
            
            if (!$proveedor) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
                exit;
            }

            $email_proveedor = $proveedor['contacto_correo'] ?? null;
            
            if (!$email_proveedor) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'El proveedor no tiene un correo electrónico registrado']);
                exit;
            }

            // Aplicar los mismos filtros de la consulta actual
            $filters = [
                'producto_nombre' => $this->post('producto_nombre') ?: $this->get('producto_nombre'),
                'rela_categoria' => $this->post('rela_categoria') ?: $this->get('rela_categoria'),
                'rela_marca' => $this->post('rela_marca') ?: $this->get('rela_marca'),
                'rela_estadoproducto' => $this->post('rela_estadoproducto') ?: $this->get('rela_estadoproducto'),
                'precio_min' => $this->post('precio_min') ?: $this->get('precio_min'),
                'precio_max' => $this->post('precio_max') ?: $this->get('precio_max'),
                'stock_min' => $this->post('stock_min') ?: $this->get('stock_min')
            ];
            
            $result = $this->productoModel->getAllWithDetailsForExport($filters);
            $productos = $result['data'];

            if (empty($productos)) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'No hay productos disponibles para cotizar']);
                exit;
            }

            // Crear archivo Excel
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Título
            $sheet->mergeCells('A1:C1');
            $sheet->setCellValue('A1', 'SOLICITUD DE COTIZACIÓN DE PRODUCTOS');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Información
            $sheet->setCellValue('A2', 'Fecha: ' . date('d/m/Y H:i'));
            $sheet->setCellValue('A3', 'Proveedor: ' . $proveedor['persona_denominacion']);
            
            // Instrucciones al principio
            $sheet->mergeCells('A5:C5');
            $sheet->setCellValue('A5', 'INSTRUCCIONES:');
            $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(10);
            
            $sheet->mergeCells('A6:C6');
            $sheet->setCellValue('A6', '• Complete la columna "Cotización" con el precio unitario de cada producto.');
            $sheet->getStyle('A6')->getFont()->setSize(9);
            
            $sheet->mergeCells('A7:C7');
            $sheet->setCellValue('A7', '• Deje VACÍA la celda de cotización para los productos que NO estén disponibles.');
            $sheet->getStyle('A7')->getFont()->setSize(9)->setBold(true)->getColor()->setRGB('FF0000');
            
            $sheet->mergeCells('A8:C8');
            $sheet->setCellValue('A8', '• Una vez completado, devuelva este archivo a nuestro correo de contacto.');
            $sheet->getStyle('A8')->getFont()->setSize(9);
            
            // Encabezados (ahora en fila 10)
            $headers = ['Descripción del producto', 'Marca', 'Cotización'];
            $sheet->fromArray($headers, null, 'A10');
            
            // Estilo encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];
            $sheet->getStyle('A10:C10')->applyFromArray($headerStyle);

            // Datos (ahora empiezan en fila 11)
            $row = 11;
            foreach ($productos as $producto) {
                $sheet->setCellValue('A' . $row, $producto['producto_nombre']);
                $sheet->setCellValue('B' . $row, $producto['marca_descripcion'] ?? 'Sin marca');
                $sheet->setCellValue('C' . $row, ''); // Columna vacía para cotización
                $row++;
            }

            // Ajustar anchos
            $sheet->getColumnDimension('A')->setWidth(50);
            $sheet->getColumnDimension('B')->setWidth(25);
            $sheet->getColumnDimension('C')->setWidth(20);

            // Bordes
            $styleArray = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            $lastRow = $row - 1;
            $sheet->getStyle('A10:C' . $lastRow)->applyFromArray($styleArray);

            // Fondo amarillo claro para columna Cotización
            $sheet->getStyle('C11:C' . $lastRow)->applyFromArray([
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFCC']
                ]
            ]);

            // Guardar archivo temporalmente
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            // Nombre de archivo: COTIZACION_<PROVEEDOR>_<FECHA>_<HORA>.xlsx
            $nombreProveedor = strtoupper(str_replace(' ', '_', $proveedor['persona_denominacion']));
            $filename = 'COTIZACION_' . $nombreProveedor . '_' . date('Ymd_His') . '.xlsx';
            $filepath = '../temp/' . $filename;
            
            if (!file_exists('../temp')) {
                mkdir('../temp', 0777, true);
            }
            
            $writer->save($filepath);

            // Enviar email
            $emailService = new \App\Core\EmailService();
            
            $asunto = 'Solicitud de Cotización de Productos - ' . date('d/m/Y');
            $mensaje = '
                <h2>Solicitud de Cotización</h2>
                <p>Estimado/a proveedor/a,</p>
                <p>Adjuntamos plantilla de cotización con el listado de productos para los cuales solicitamos su mejor oferta de precios.</p>
                <p>Detalles de la solicitud:</p>
                <ul>
                    <li><strong>Fecha:</strong> ' . date('d/m/Y H:i') . '</li>
                    <li><strong>Total de productos:</strong> ' . count($productos) . '</li>
                </ul>
                <p>Por favor, complete los precios solicitados y devuelva el archivo a nuestro correo de contacto.</p>
                <p>Quedamos a la espera de su respuesta.</p>
                <p>Saludos cordiales,<br>Sistema de Gestión de Cabañas</p>
            ';

            $result = $emailService->sendEmailWithAttachment(
                $email_proveedor,
                $proveedor['persona_denominacion'],
                $asunto,
                $mensaje,
                '',  // textBody vacío
                $filepath
            );

            // Eliminar archivo temporal
            if (file_exists($filepath)) {
                unlink($filepath);
            }

            if ($result['success']) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cotización enviada exitosamente a ' . $proveedor['persona_denominacion']
                ]);
                exit;
            } else {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al enviar el correo electrónico: ' . $result['message']
                ]);
                exit;
            }

        } catch (\Exception $e) {
            error_log("Error al enviar cotización: " . $e->getMessage());
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Error al enviar cotización: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    /**
     * Obtener texto del estado
     */
    private function getEstadoTexto($estado)
    {
        $estados = [
            1 => 'Disponible',
            2 => 'Stock Mínimo',
            3 => 'Sin Stock',
            4 => 'Baja'
        ];

        return $estados[$estado] ?? 'Desconocido';
    }
}

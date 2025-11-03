<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Models\EstadoProducto;

/**
 * Controlador para la gestión de productos
 */
class ProductosController extends Controller
{
    protected $productoModel;
    protected $categoriaModel;
    protected $marcaModel;
    protected $estadoProductoModel;

    public function __construct()
    {
        parent::__construct();
        $this->productoModel = new Producto();
        $this->categoriaModel = new Categoria();
        $this->marcaModel = new Marca();
        $this->estadoProductoModel = new EstadoProducto();
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

        $data = [
            'title' => 'Gestión de Productos',
            'productos' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'categorias' => $categorias,
            'marcas' => $marcas,
            'estadosProducto' => $estadosProducto,
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

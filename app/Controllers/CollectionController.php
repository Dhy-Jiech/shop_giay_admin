<?php
// app/Controllers/CollectionController.php
require_once dirname(__DIR__) . '/Core/Controller.php';
require_once dirname(__DIR__) . '/Models/Collection.php';
require_once dirname(__DIR__) . '/Models/Product.php';

class CollectionController extends Controller
{
    private $collectionModel;
    private $productModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: /shop_giay_admin/public/?url=login');
            exit;
        }
        $this->collectionModel = new Collection();
        $this->productModel = new Product();
    }

    public function index()
    {
        $collections = $this->collectionModel->getAll();

        // Thêm trường số lượng sản phẩm vào mỗi collection
        foreach ($collections as &$col) {
            $products = $this->collectionModel->getProducts($col['id']);
            $col['product_count'] = count($products);
        }

        $this->view('admin/collections', [
            'title' => 'Quản lý Collection',
            'collections' => $collections
        ]);
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $slug = $this->createSlug($name);
            $description = trim($_POST['description'] ?? '');
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');
            $status = isset($_POST['status']) ? 1 : 0;
            $banner_image = '';

            // Handle file upload
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/collections/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES['banner_image']['name']);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadFile)) {
                    $banner_image = '/uploads/collections/' . $fileName;
                }
            }

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'banner_image' => $banner_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ];

            if ($this->collectionModel->create($data)) {
                $this->redirect('/admin/collections');
            }
            else {
                echo "Error creating collection";
            }
        }
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $slug = $this->createSlug($name);
            $description = trim($_POST['description'] ?? '');
            $start_date = trim($_POST['start_date'] ?? '');
            $end_date = trim($_POST['end_date'] ?? '');
            $status = isset($_POST['status']) ? 1 : 0;

            $existingCollection = $this->collectionModel->getById($id);
            $banner_image = $existingCollection['banner_image'];

            // Handle file upload
            if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/collections/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileName = time() . '_' . basename($_FILES['banner_image']['name']);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $uploadFile)) {
                    $banner_image = '/uploads/collections/' . $fileName;
                    // Xoá ảnh cũ (tuỳ chọn)
                    if (!empty($existingCollection['banner_image'])) {
                        $oldFilePath = dirname(dirname(__DIR__)) . '/public' . $existingCollection['banner_image'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath);
                        }
                    }
                }
            }

            $data = [
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'banner_image' => $banner_image,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ];

            if ($this->collectionModel->update($id, $data)) {
                $this->redirect('/admin/collections');
            }
            else {
                echo "Error updating collection";
            }
        }
    }

    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Có thể xoá ảnh banner thực tế khỏi server nếu muốn
            // $collection = $this->collectionModel->getById($id);
            // ... (unlink code)

            if ($this->collectionModel->delete($id)) {
                $this->redirect('/admin/collections');
            }
            else {
                echo "Error deleting collection";
            }
        }
    }

    public function toggleStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');
            if ($this->collectionModel->toggleStatus($id)) {
                echo json_encode(['success' => true]);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Lỗi khi cập nhật trạng thái']);
            }
            exit;
        }
    }

    // --- API cho Ajax (Quản lý sản phẩm trong Collection) ---

    // Lấy danh sách tát cả sp (để chọn) & danh sách sp đã chọn
    public function getProductsData($collectionId)
    {
        header('Content-Type: application/json');

        try {
            // Lấy tất cả sản phẩm
            $allProducts = $this->productModel->findAll();

            // Lấy id các sản phẩm đã có trong collection này
            $selectedProductIds = $this->collectionModel->getProductIds($collectionId);

            echo json_encode([
                'success' => true,
                'allProducts' => $allProducts,
                'selectedProductIds' => $selectedProductIds
            ]);
        }
        catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    // Lưu danh sách sản phẩm cho collection
    public function syncProducts($collectionId)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            header('Content-Type: application/json');

            $json = file_get_contents('php://input');
            $data = json_decode($json, true);

            $productIds = isset($data['productIds']) ? $data['productIds'] : [];

            if ($this->collectionModel->syncProducts($collectionId, $productIds)) {
                echo json_encode(['success' => true, 'message' => 'Đã lưu danh sách sản phẩm thành công']);
            }
            else {
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi lưu']);
            }
            exit;
        }
    }

    private function createSlug($string)
    {
        $search = array(
            '#(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)#',
            '#(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)#',
            '#(ì|í|ị|ỉ|ĩ)#',
            '#(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)#',
            '#(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)#',
            '#(ỳ|ý|ỵ|ỷ|ỹ)#',
            '#(đ)#',
            '#(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)#',
            '#(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)#',
            '#(Ì|Í|Ị|Ỉ|Ĩ)#',
            '#(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ữ)#',
            '#(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)#',
            '#(Ỳ|Ý|Ỵ|Ỷ|Ỹ)#',
            '#(Đ)#',
            '/[^a-zA-Z0-9\-\_]/',
        );
        $replace = array(
            'a', 'e', 'i', 'o', 'u', 'y', 'd',
            'A', 'E', 'I', 'O', 'U', 'Y', 'D',
            '-',
        );
        $string = preg_replace($search, $replace, $string);
        $string = preg_replace('/(-)+/', '-', $string);
        $string = strtolower($string);
        return $string;
    }

    private function redirect($url)
    {
        header("Location: /shop_giay_admin/public/?url=" . ltrim($url, '/'));
        exit;
    }
}

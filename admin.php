<?php
// admin.php
include 'php/db_connect.php';

// Fetch all products
$productsResult = $mysqli->query("SELECT p.*, c.category_name FROM products p LEFT JOIN categories c ON p.category_id = c.category_id ORDER BY p.product_id DESC");
$products = $productsResult->fetch_all(MYSQLI_ASSOC);

// Fetch all users
$usersResult = $mysqli->query("SELECT u.*, r.role_name FROM users u LEFT JOIN user_roles r ON u.role_id = r.role_id ORDER BY u.user_id DESC");
$users = $usersResult->fetch_all(MYSQLI_ASSOC);

// Fetch support messages
$messagesResult = $mysqli->query("SELECT * FROM contact_messages ORDER BY message_id DESC");
$messages = $messagesResult->fetch_all(MYSQLI_ASSOC);

// Fetch categories for admin select inputs
$categoriesResult = $mysqli->query("SELECT * FROM categories ORDER BY category_id ASC");
$categories = $categoriesResult->fetch_all(MYSQLI_ASSOC);

// NOTE for maintainers:
// Categories are stored in the `categories` table (columns: category_id, category_name, slug).
// - To change category display names, edit `category_name` in the `categories` table (via SQL or a DB admin tool).
// - To change the URL-friendly value used for filtering, edit the `slug` column.
// - If you localize category names, either add translation keys in `translations/*.json` and map them when rendering,
//   or update `category_name` with the localized string. The product filters use category slugs (e.g., 'aquaculture').
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Admin Dashboard - RAWEE</title>
<link rel="stylesheet" href="css/style.css" />
<link rel="stylesheet" href="css/admin.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
<style>
/* Modal style like login_handler */
.modal {
  display: none;
  position: fixed;
  z-index: 999;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0,0,0,0.6);
  animation: fadeIn 0.3s;
}
.modal-content {
  background-color: #fff;
  margin: 10% auto;
  padding: 30px;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
  box-shadow: 0 8px 24px rgba(0,0,0,0.3);
  animation: slideIn 0.3s;
  position: relative;
}
.close-button {
  color: #aaa;
  position: absolute;
  top: 15px;
  right: 20px;
  font-size: 24px;
  font-weight: bold;
  cursor: pointer;
}
.close-button:hover { color: #000; }
.modal form input,
.modal form textarea,
.modal form select {
  width: 100%;
  margin-bottom: 15px;
  padding: 10px;
  border-radius: 8px;
  border: 1px solid #ccc;
}
.modal form button {
  width: 100%;
  padding: 12px;
  border: none;
  background-color: #077A7D;
  color: #fff;
  font-weight: 600;
  border-radius: 8px;
  cursor: pointer;
}
.modal form button:hover { background-color: #077A7D; }
@keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
@keyframes slideIn { from {transform: translateY(-30px); opacity:0;} to {transform: translateY(0); opacity:1;} }
.modal form label {
  display: inline-flex;
  align-items: center;
  margin-right: 15px;
  cursor: pointer;
}
.modal form label input[type="checkbox"] { margin-right: 10px; }
</style>
</head>
<body>
<div class="admin-layout">
  <aside class="admin-sidebar">
    <div class="sidebar-header">
      <a href="index.php" class="sidebar-logo">RAWEE</a>
      <span class="sidebar-role">Admin Panel</span>
    </div>
    <nav class="sidebar-nav">
      <a href="#dashboard" class="nav-link active" onclick="showPanel('dashboard', this)"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
      <a href="#users" class="nav-link" onclick="showPanel('users', this)"><i class="fas fa-users"></i><span>User Management</span></a>
      <a href="#products" class="nav-link" onclick="showPanel('products', this)"><i class="fas fa-box-open"></i><span>Product Management</span></a>
      <a href="#support" class="nav-link" onclick="showPanel('support', this)"><i class="fas fa-headset"></i><span>Support</span></a>
    </nav>
    <div class="sidebar-footer">
      <div class="admin-profile">
        <img src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?q=80&w=100&auto=format&fit=crop" alt="Admin Avatar"/>
        <div class="admin-info">
          <strong>Admin User</strong>
          <span>admin@rawee.tech</span>
        </div>
      </div>
      <a href="index.php" class="logout-button"><i class="fas fa-sign-out-alt"></i></a>
    </div>
  </aside>

  <main class="admin-main-content">
    <!-- Dashboard -->
    <div id="dashboard" class="admin-panel active">
      <h1 class="panel-title">Dashboard Overview</h1>
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon users"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <span class="stat-value"><?= count($users) ?></span>
            <span class="stat-label">Total Users</span>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon products"><i class="fas fa-box-open"></i></div>
          <div class="stat-info">
            <span class="stat-value"><?= count($products) ?></span>
            <span class="stat-label">Total Products</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Users -->
    <div id="users" class="admin-panel">
      <h1 class="panel-title">User Management</h1>
      <div class="table-container">
        <table>
          <thead><tr><th>User ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($users as $u): ?>
            <tr>
              <td><?= $u['user_id'] ?></td>
              <td><?= htmlspecialchars($u['full_name']) ?></td>
              <td><?= htmlspecialchars($u['email']) ?></td>
              <td><?= $u['role_name'] ?></td>
              <td><?= $u['created_at'] ?></td>
              <td class="actions">
                <button class="btn-action edit"><i class="fas fa-key"></i> Reset Pass</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Products -->
    <div id="products" class="admin-panel">
      <div class="panel-header">
        <h1 class="panel-title">Product Management</h1>
        <button id="addProductBtn" class="btn-primary-admin"><i class="fas fa-plus"></i> Add Product</button>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach($products as $p): ?>
            <tr>
              <?php
                // Normalize image preview URL for admin table
                $adminImg = 'images/default_product.png';
                if (!empty($p['image_url']) && $p['image_url'] != '0') {
                  $rawImg = $p['image_url'];
                  if (preg_match('/^https?:\/\//i', $rawImg)) {
                    $adminImg = $rawImg;
                  } elseif (strpos($rawImg, 'uploads/') === 0 || strpos($rawImg, '/uploads/') === 0) {
                    $adminImg = $rawImg;
                  } else {
                    $adminImg = 'uploads/' . $rawImg;
                  }
                }
              ?>
              <td><img src="<?= $adminImg ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="50" style="border:1px solid #ccc; padding:2px;" /></td>
                <td><?= htmlspecialchars($p['name']) ?></td>
              <td><?= $p['category_name'] ?></td>
              <td>$<?= $p['price'] ?></td>
              <td><?= $p['in_stock'] ? 'Yes' : 'No' ?></td>
                <td class="actions">
                <button class="btn-action edit" onclick="openEditModal(<?= $p['product_id'] ?>,'<?= addslashes($p['name']) ?>','<?= addslashes($p['description']) ?>','<?= $p['price'] ?>','<?= $p['category_id'] ?>','<?= addslashes($adminImg) ?>','<?= $p['in_stock'] ?>','<?= $p['is_new'] ?>','<?= addslashes($p['detailed_description'] ?? '') ?>','<?= addslashes($p['detailed_description_ar'] ?? '') ?>')"><i class="fas fa-edit"></i></button>
                <button class="btn-action delete" onclick="deleteProduct(<?= $p['product_id'] ?>)"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Support -->
    <div id="support" class="admin-panel">
      <h1 class="panel-title">Support & Communications</h1>
      <div class="table-container">
        <table>
          <thead><tr><th>From</th><th>Subject</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($messages as $m): ?>
            <tr>
              <td><?= htmlspecialchars($m['full_name']) ?></td>
              <td><?= htmlspecialchars($m['subject']) ?></td>
              <td><?= $m['submitted_at'] ?></td>
              <td><?= $m['status'] ?></td>
              <td><button class="btn-action view"><i class="fas fa-eye"></i> View</button></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<!-- ======================= Add Product Modal (Corrected) ======================= -->
<div id="addProductModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeAddModal()">&times;</span>
        <h2>Add New Product</h2>
        <form action="php/admin_add_product.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="Product Name" required />
            
            <!-- Short Description -->
            <textarea name="description" placeholder="Short Description (for product cards)"></textarea>
            
            <!-- Detailed Descriptions (EN & AR) -->
            <label>Detailed Description (English)</label>
            <textarea name="detailed_description_en" placeholder="Detailed Description (English)" style="height: 120px;"></textarea>
            <label>Detailed Description (Arabic)</label>
            <textarea name="detailed_description_ar" placeholder="تفاصيل المنتج (بالعربية)" style="height: 120px;"></textarea>
            <div style="margin:8px 0; text-align:right;"><button type="button" class="btn-primary-admin" onclick="copyArToEn('add')">Copy AR → EN</button></div>
            <!-- ============================================================================ -->

      <input type="number" step="0.01" name="price" placeholder="Price" required />
      <label>Category</label>
      <select name="category_id" id="add_category_id" required>
        <option value="">-- Select Category --</option>
        <?php foreach($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
            <label>Product Image</label>
            <input type="file" name="image_file" id="addImageInput" accept="image/*" />
            <div id="addImagePreview" style="margin-top:10px;">
                <img id="addPreviewImg" src="" alt="Image Preview" style="max-width:150px; display:none;" />
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <label><input type="checkbox" name="in_stock" value="1" /> In Stock</label>
                <label><input type="checkbox" name="is_new" value="1" /> New</label>
            </div>
            <button type="submit" class="btn-primary-admin" style="margin-top:15px;">Add Product</button>
        </form>
    </div>
</div>

<!-- ======================= Edit Product Modal (Corrected) ======================= -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <span class="close-button" onclick="closeEditModal()">&times;</span>
        <h2>Edit Product</h2>
        <form action="php/admin_edit_product.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" id="edit_product_id" name="product_id" />
            <input type="text" id="edit_name" name="name" placeholder="Product Name" required />

            <!-- Short Description -->
            <textarea id="edit_description" name="description" placeholder="Short Description"></textarea>

            <!-- Detailed Descriptions (EN & AR) -->
            <label>Detailed Description (English)</label>
            <textarea id="edit_detailed_description_en" name="detailed_description_en" placeholder="Detailed Description (English)" style="height: 120px;"></textarea>
            <label>Detailed Description (Arabic)</label>
            <textarea id="edit_detailed_description_ar" name="detailed_description_ar" placeholder="تفاصيل المنتج (بالعربية)" style="height: 120px;"></textarea>
            <div style="margin:8px 0; text-align:right;"><button type="button" class="btn-primary-admin" onclick="copyArToEn('edit')">Copy AR → EN</button></div>
            <!-- ============================================================================ -->

      <input type="number" step="0.01" id="edit_price" name="price" placeholder="Price" required />
      <label>Category</label>
      <select name="category_id" id="edit_category_id" required>
        <option value="">-- Select Category --</option>
        <?php foreach($categories as $cat): ?>
          <option value="<?= $cat['category_id'] ?>"><?= htmlspecialchars($cat['category_name']) ?></option>
        <?php endforeach; ?>
      </select>
            <label>Product Image</label>
            <input type="file" name="image_file" id="editImageInput" accept="image/*" />
            <div id="editImagePreview" style="margin-top:10px;">
                <img id="editPreviewImg" src="" alt="Image Preview" style="max-width:150px;" />
            </div>
            <div style="display:flex; gap:10px; margin-top:10px;">
                <label><input type="checkbox" id="edit_in_stock" name="in_stock" value="1" /> In Stock</label>
                <label><input type="checkbox" id="edit_is_new" name="is_new" value="1" /> New</label>
            </div>
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

<script>
function showPanel(panelId, element){
  document.querySelectorAll(".admin-panel").forEach(p=>p.classList.remove("active"));
  document.getElementById(panelId).classList.add("active");
  document.querySelectorAll(".sidebar-nav .nav-link").forEach(l=>l.classList.remove("active"));
  element.classList.add("active");
}

// Add Product Modal
const addModal = document.getElementById("addProductModal");
document.getElementById("addProductBtn").onclick = ()=> addModal.style.display="block";
function closeAddModal(){ addModal.style.display="none"; }

// Add Image Preview
const addImageInput = document.getElementById('addImageInput');
const addPreviewImg = document.getElementById('addPreviewImg');
addImageInput.addEventListener('change', function(){
  const file = this.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = function(e){
      addPreviewImg.setAttribute('src', e.target.result);
      addPreviewImg.style.display = 'block';
    }
    reader.readAsDataURL(file);
  } else { addPreviewImg.style.display='none'; }
});

// Edit Product Modal
const editModal = document.getElementById("editProductModal");
function closeEditModal(){ editModal.style.display="none"; }
function openEditModal(id,name,desc,price,cat,image,stock,isNew,detailed_en,detailed_ar){
  document.getElementById("edit_product_id").value=id;
  document.getElementById("edit_name").value=name;
  document.getElementById("edit_description").value=desc;
  document.getElementById("edit_price").value=price;
  document.getElementById("edit_category_id").value=cat;
  document.getElementById("edit_in_stock").checked=stock==1;
  document.getElementById("edit_is_new").checked=isNew==1;

  // Populate detailed descriptions if available
  if(typeof detailed_en !== 'undefined'){
    document.getElementById('edit_detailed_description_en').value = detailed_en;
  } else {
    document.getElementById('edit_detailed_description_en').value = '';
  }
  if(typeof detailed_ar !== 'undefined'){
    document.getElementById('edit_detailed_description_ar').value = detailed_ar;
  } else {
    document.getElementById('edit_detailed_description_ar').value = '';
  }

  // Set existing image in preview
  const editPreviewImg = document.getElementById("editPreviewImg");
  editPreviewImg.setAttribute('src', image);
  
  // Clear file input
  document.getElementById("editImageInput").value = '';
  
  editModal.style.display="block";
}

// Copy Arabic -> English helper for add/edit modals
function copyArToEn(mode){
  if(mode === 'add'){
    const ar = document.querySelector('textarea[name="detailed_description_ar"]').value;
    document.querySelector('textarea[name="detailed_description_en"]').value = ar;
  } else if(mode === 'edit'){
    const ar = document.getElementById('edit_detailed_description_ar').value;
    document.getElementById('edit_detailed_description_en').value = ar;
  }
}

// Edit Image Preview
const editImageInput = document.getElementById('editImageInput');
editImageInput.addEventListener('change', function(){
  const file = this.files[0];
  if(file){
    const reader = new FileReader();
    reader.onload = function(e){
      document.getElementById('editPreviewImg').setAttribute('src', e.target.result);
    }
    reader.readAsDataURL(file);
  }
});

window.onclick = function(event){
  if(event.target==addModal) closeAddModal();
  if(event.target==editModal) closeEditModal();
};

// Delete Product
function deleteProduct(id){
  if(confirm("Are you sure you want to delete this product?")){
    fetch('php/admin_delete_product.php',{
      method:'POST',
      headers:{'Content-Type':'application/x-www-form-urlencoded'},
      body:'product_id='+id
    })
    .then(res=>res.text())
    .then(res=>{
      if(res.trim()=='success') location.reload();
      else alert('Failed to delete product');
    });
  }
}
</script>
</body>
</html>

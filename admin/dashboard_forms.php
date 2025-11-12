<?php 
/**
 * Admin Dashboard Forms Snippet
 * Included by dashboard.php (which is in /admin/)
 */

// Note: $mock_categories is expected to be defined in dashboard.php
// which is standard for an included file.
?>
<div class="bg-card-bg p-6 rounded-xl shadow-xl">
    <h3 class="text-2xl font-bold text-primary mb-4">Add New User</h3>
    <form action="api/user_crud.php" method="POST" class="space-y-4" id="add-user-form"> 
        <input type="hidden" name="action" value="add_user">
        
        <input type="text" name="new_username" required placeholder="Username" 
               class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white">
        
        <input type="email" name="new_email" required placeholder="Email Address" 
               class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white">
        
        <input type="password" name="new_password" required placeholder="Password" 
               class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white">
        
        <button type="submit" class="w-full bg-success hover:bg-green-600 text-white font-bold py-3 rounded-xl transition">
            <i class="fas fa-user-plus mr-2"></i> Create User
        </button>
    </form>
</div>

<div class="bg-card-bg p-6 rounded-xl shadow-xl">
    <h3 class="text-2xl font-bold text-primary mb-4">Add New Product</h3>
    <form action="api/product_crud_api.php" method="POST" class="space-y-4" id="add-product-form"> 
        <input type="hidden" name="action" value="add_product">
        <input type="text" name="product_name" required placeholder="Product Name" 
               class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white">
        
        <select name="product_category" required class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white appearance-none">
            <option value="">Select Category</option>
            <?php foreach ($mock_categories as $cat): ?>
                <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
            <?php endforeach; ?>
        </select>
        
        <input type="number" step="0.01" name="product_price" required placeholder="Price (e.g., 999.00)" 
               class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white">
        
        <textarea name="product_description" rows="3" placeholder="Product Description"
                  class="w-full p-3 bg-dark-bg border border-gray-600 rounded-lg text-white"></textarea>
                  
        <label class="block text-sm text-gray-400 pt-2">Product Image (Mock upload)</label>
        <input type="file" name="product_image" 
               class="w-full text-sm text-gray-300 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-white hover:file:bg-teal-700"/>
        
        <div class="flex items-center space-x-3 pt-2">
            <input type="checkbox" id="is_exclusive_offer" name="is_exclusive_offer" value="1"
                   class="h-4 w-4 text-primary bg-dark-bg border-gray-600 rounded focus:ring-primary">
            <label for="is_exclusive_offer" class="text-gray-300 font-medium">Exclusive Offer?</label>
        </div>

        <button type="submit" class="w-full bg-success hover:bg-green-600 text-white font-bold py-3 rounded-xl transition">
            <i class="fas fa-plus-circle mr-2"></i> Create Product
        </button>
    </form>
</div>
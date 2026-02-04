<?php
// search.php - Product search page
$page_title = "Search Products - SBSmart";
$meta_description = "Search for industrial and electrical supplies at SBSmart";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/db.php';

// Get search query
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$products = [];
$total_results = 0;

if (!empty($query)) {
    try {
        $pdo = get_db();
        
        // Search in product name, description, brand, model, and SKU
        $search_term = '%' . $query . '%';
        
        $sql = "SELECT p.*, 
                       c.name as category_name,
                       s.name as subcategory_name
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN subcategories s ON p.subcategory_id = s.id
                WHERE p.status = 1
                  AND (p.title LIKE :search1 
                   OR p.description LIKE :search2
                   OR p.sku LIKE :search3)
                ORDER BY 
                    CASE 
                        WHEN p.title LIKE :search4 THEN 1
                        WHEN p.sku LIKE :search5 THEN 2
                        ELSE 3
                    END,
                    p.title ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'search1' => $search_term,
            'search2' => $search_term,
            'search3' => $search_term,
            'search4' => $search_term,
            'search5' => $search_term
        ]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $total_results = count($products);
        
        // Debug output (remove after fixing)
        if (isset($_GET['debug'])) {
            echo "<pre>Debug Info:\n";
            echo "Query: $query\n";
            echo "Search Term: $search_term\n";
            echo "SQL: $sql\n";
            echo "Results: $total_results\n";
            echo "Products: ";
            print_r($products);
            echo "</pre>";
        }
        
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        if (isset($_GET['debug'])) {
            echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<main class="py-4">
    <div class="container">
        <!-- Search Header -->
        <div class="mb-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search Results</li>
                </ol>
            </nav>
            
            <?php if (!empty($query)): ?>
                <h1 class="h3 mb-2">Search Results for "<?= htmlspecialchars($query, ENT_QUOTES) ?>"</h1>
                <p class="text-muted">Found <?= $total_results ?> product<?= $total_results !== 1 ? 's' : '' ?></p>
            <?php else: ?>
                <h1 class="h3 mb-2">Search Products</h1>
                <p class="text-muted">Enter a search term to find products</p>
            <?php endif; ?>
        </div>

        <?php if (empty($query)): ?>
            <!-- Empty search state -->
            <div class="text-center py-5">
                <i class="bi bi-search" style="font-size: 4rem; color: #ddd;"></i>
                <h2 class="h4 mt-3 mb-2">Start Your Search</h2>
                <p class="text-muted mb-4">Enter keywords to find products, brands, or model numbers</p>
                
                <!-- Search form -->
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <form action="<?= get_base_url() ?>search.php" method="get" class="d-flex gap-2">
                            <input type="search" name="q" class="form-control" placeholder="Search products..." required autofocus>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php elseif ($total_results === 0): ?>
            <!-- No results state -->
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 4rem; color: #ddd;"></i>
                <h2 class="h4 mt-3 mb-2">No Results Found</h2>
                <p class="text-muted mb-4">We couldn't find any products matching "<?= htmlspecialchars($query, ENT_QUOTES) ?>"</p>
                
                <div class="mb-3">
                    <strong>Suggestions:</strong>
                    <ul class="list-unstyled mt-2 text-muted">
                        <li>Check your spelling</li>
                        <li>Try different keywords</li>
                        <li>Use more general terms</li>
                        <li>Try searching by brand or model number</li>
                    </ul>
                </div>
                
                <!-- Try another search -->
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <form action="<?= get_base_url() ?>search.php" method="get" class="d-flex gap-2">
                            <input type="search" name="q" class="form-control" placeholder="Try another search..." value="<?= htmlspecialchars($query, ENT_QUOTES) ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Results grid -->
            <div class="row g-3 g-md-4">
                <?php foreach ($products as $product): 
                    $id = (int)$product['id'];
                    $name = htmlspecialchars($product['title'], ENT_QUOTES);
                    $price = number_format((float)$product['price'], 2);
                    $mrp = isset($product['mrp']) && $product['mrp'] > 0 ? number_format((float)$product['mrp'], 2) : null;
                    
                    // Use the central resolve_image helper
                    $image = resolve_image($product['image'] ?? '');
                    
                    // Category breadcrumb
                    $breadcrumb = [];
                    if (!empty($product['category_name'])) {
                        $breadcrumb[] = htmlspecialchars($product['category_name'], ENT_QUOTES);
                    }
                    if (!empty($product['subcategory_name'])) {
                        $breadcrumb[] = htmlspecialchars($product['subcategory_name'], ENT_QUOTES);
                    }
                    $breadcrumb_text = implode(' › ', $breadcrumb);
                ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="product-card h-100 d-flex flex-column">
                        <div class="skeleton-box">
                            <a href="product.php?id=<?= $id ?>">
                                <img src="<?= htmlspecialchars($image, ENT_QUOTES) ?>" 
                                     class="card-img-top" 
                                     alt="<?= $name ?>"
                                     loading="lazy"
                                     onerror="this.src='<?= esc(resolve_image('')) ?>'">
                            </a>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <?php if ($breadcrumb_text): ?>
                                <div class="text-muted small mb-2" style="font-size: 0.75rem;">
                                    <?= $breadcrumb_text ?>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="card-title">
                                <a href="product.php?id=<?= $id ?>" class="text-decoration-none">
                                    <?php 
                                        $dName = !empty($product['sku']) ? $product['sku'] : $name;
                                        $dSub = $product['subcategory_name'] ?? '';
                                    ?>
                                    <?= htmlspecialchars($dName, ENT_QUOTES) ?>
                                    <?php if(!empty($dSub)): ?>
                                        <br><span class="text-muted small fw-normal" style="font-size:0.75rem;">(<?= htmlspecialchars($dSub, ENT_QUOTES) ?>)</span>
                                    <?php endif; ?>
                                </a>
                            </h5>
                            
                            <div class="product-price mt-auto">
                                <?php if ($product['price'] > 0): ?>
                                    ₹<?= $price ?>
                                    <?php if ($mrp && $mrp > $price): ?>
                                        <span class="product-mrp">₹<?= $mrp ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-primary small fw-bold">Price on Request</span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="product.php?id=<?= $id ?>" class="btn btn-view w-100 mt-2">
                                <?php if ($product['price'] > 0): ?>
                                    View Details
                                <?php else: ?>
                                    Request Quote
                                <?php endif; ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Refine search -->
            <div class="mt-5 pt-4 border-top">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <h3 class="h5 mb-3 text-center">Refine Your Search</h3>
                        <form action="<?= get_base_url() ?>search.php" method="get" class="d-flex gap-2">
                            <input type="search" name="q" class="form-control" placeholder="Search again..." value="<?= htmlspecialchars($query, ENT_QUOTES) ?>" required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Search
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

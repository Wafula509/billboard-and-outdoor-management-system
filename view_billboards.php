<?php
session_start();
$pageTitle = "Billboard Inventory - Billboard Solutions";
include 'header.php';
include 'db_connect.php';

// Initialize filter variables with proper sanitization
$availability_filter = isset($_GET['availability']) ? $conn->real_escape_string($_GET['availability']) : 'all';
$location_filter = isset($_GET['location']) ? $conn->real_escape_string($_GET['location']) : '';

// Build the base query with prepared statements
$query = "SELECT * FROM billboards WHERE 1=1";
$params = [];
$types = '';

// Apply filters
if ($availability_filter !== 'all') {
    $query .= " AND availability = ?";
    $params[] = $availability_filter;
    $types .= 's';
}

if (!empty($location_filter)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location_filter%";
    $types .= 's';
}

// Add sorting
$query .= " ORDER BY availability DESC, location ASC";

// Prepare and execute the statement
$stmt = $conn->prepare($query);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
    :root {
        --primary-color: #2c3e50;
        --secondary-color: #f4a261;
        --accent-color: #e76f51;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --transition-speed: 0.3s;
    }

    .billboard-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('images/billboard-hero.jpg') no-repeat center center/cover;
        color: white;
        text-align: center;
        padding: 100px 20px;
        margin-bottom: 40px;
    }

    .billboard-hero h1 {
        font-size: 2.8rem;
        margin-bottom: 20px;
        color: var(--secondary-color);
    }

    .billboard-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 20px 40px;
        box-sizing: border-box;
    }

    .filter-section {
        background: white;
        padding: 25px;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .filter-section h2 {
        color: var(--primary-color);
        margin-bottom: 20px;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .filter-section h2 i {
        color: var(--secondary-color);
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        align-items: end;
    }

    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .form-control {
        width: 100%;
        padding: 10px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1rem;
        transition: all var(--transition-speed);
    }

    .form-control:focus {
        border-color: var(--secondary-color);
        outline: none;
        box-shadow: 0 0 0 3px rgba(244, 162, 97, 0.2);
    }

    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-speed);
        text-decoration: none;
        gap: 8px;
    }

    .btn-primary {
        background: var(--secondary-color);
        color: white;
    }

    .btn-primary:hover {
        background: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .btn-outline {
        background: transparent;
        border: 1px solid #ddd;
        color: var(--dark-color);
    }

    .btn-outline:hover {
        background: #f8f9fa;
    }

    .billboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 25px;
        margin-bottom: 40px;
    }

    .billboard-card {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 3px 15px rgba(0, 0, 0, 0.1);
        transition: all var(--transition-speed);
    }

    .billboard-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    }

    .billboard-image-container {
        position: relative;
        height: 220px;
        overflow: hidden;
    }

    .billboard-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.5s ease;
    }

    .billboard-card:hover .billboard-image {
        transform: scale(1.05);
    }

    .availability-badge {
        position: absolute;
        top: 15px;
        right: 15px;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        z-index: 1;
    }

    .available {
        background: var(--success-color);
        color: white;
    }

    .unavailable {
        background: var(--danger-color);
        color: white;
    }

    .billboard-details {
        padding: 20px;
    }

    .billboard-title {
        font-size: 1.3rem;
        font-weight: 700;
        margin: 0 0 10px 0;
        color: var(--primary-color);
    }

    .billboard-info {
        display: flex;
        align-items: center;
        margin: 8px 0;
        font-size: 0.95rem;
        color: var(--dark-color);
    }

    .billboard-info i {
        width: 20px;
        color: var(--secondary-color);
        margin-right: 10px;
        text-align: center;
    }

    .price {
        font-weight: 700;
        color: var(--success-color);
        font-size: 1.2rem;
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .action-buttons .btn {
        flex: 1;
        padding: 8px 12px;
        font-size: 0.9rem;
    }

    .btn-edit {
        background: var(--info-color);
        color: white;
    }

    .btn-edit:hover {
        background: #138496;
    }

    .btn-danger {
        background: var(--danger-color);
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .no-billboards {
        text-align: center;
        padding: 60px 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
    }

    .no-billboards i {
        font-size: 3.5rem;
        color: var(--secondary-color);
        margin-bottom: 20px;
    }

    .no-billboards p {
        font-size: 1.2rem;
        color: var(--dark-color);
        margin-bottom: 20px;
    }

    .add-new-container {
        text-align: center;
        margin-top: 30px;
    }

    .btn-add-new {
        padding: 12px 25px;
        font-size: 1.1rem;
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    @media (max-width: 768px) {
        .billboard-hero h1 {
            font-size: 2.2rem;
        }
        
        .filter-form {
            grid-template-columns: 1fr;
        }
        
        .billboard-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .billboard-hero {
            padding: 80px 20px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn {
            width: 100%;
        }
    }
</style>

<!-- Hero Section -->
<section class="billboard-hero" aria-label="Billboard inventory header">
    <h1><i class="fas fa-ad"></i> Our Billboard Inventory</h1>
    <p>Discover premium advertising spaces across the region</p>
</section>

<div class="billboard-container">
    <!-- Filter Section -->
    <div class="filter-section">
        <h2><i class="fas fa-filter"></i> Filter Billboards</h2>
        <form method="get" action="" class="filter-form">
            <div class="form-group">
                <label for="availability">Availability</label>
                <select name="availability" id="availability" class="form-control">
                    <option value="all">All Statuses</option>
                    <option value="Available" <?php echo ($availability_filter === 'Available') ? 'selected' : ''; ?>>Available</option>
                    <option value="Unavailable" <?php echo ($availability_filter === 'Unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" class="form-control" 
                       placeholder="Search by location" value="<?php echo htmlspecialchars($location_filter); ?>">
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Apply Filters
                </button>
                <a href="view_billboards.php" class="btn btn-outline" aria-label="Reset filters">
                    <i class="fas fa-undo"></i> Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Billboard Grid -->
    <?php if ($result->num_rows > 0): ?>
        <div class="billboard-grid">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="billboard-card">
                    <div class="billboard-image-container">
                        <?php if (!empty($row['image'])): ?>
                            <img src="<?php echo htmlspecialchars($row['image']); ?>" alt="Billboard at <?php echo htmlspecialchars($row['location']); ?>" class="billboard-image" loading="lazy">
                        <?php else: ?>
                            <img src="images/default-billboard.jpg" alt="Default billboard image" class="billboard-image" loading="lazy">
                        <?php endif; ?>
                        <span class="availability-badge <?php echo strtolower($row['availability']); ?>">
                            <?php echo htmlspecialchars($row['availability']); ?>
                        </span>
                    </div>
                    
                    <div class="billboard-details">
                        <h3 class="billboard-title"><?php echo htmlspecialchars($row['location']); ?></h3>
                        
                        <div class="billboard-info">
                            <i class="fas fa-ruler-combined"></i>
                            <span>Size: <?php echo htmlspecialchars($row['size']); ?></span>
                        </div>
                        
                        <div class="billboard-info">
                            <i class="fas fa-tag"></i>
                            <span>Type: <?php echo htmlspecialchars($row['type'] ?? 'Standard'); ?></span>
                        </div>
                        
                        <div class="billboard-info">
                            <i class="fas fa-eye"></i>
                            <span>Visibility: <?php echo htmlspecialchars($row['visibility'] ?? 'High'); ?></span>
                        </div>
                        
                        <p class="price">
                            <i class="fas fa-money-bill-wave"></i> 
                            KES <?php echo number_format($row['price'], 2); ?> per day
                        </p>
                        
                        
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-billboards">
            <i class="fas fa-ad"></i>
            <p>No billboards match your search criteria</p>
            <a href="view_billboards.php" class="btn btn-primary" aria-label="Reset filters">
                <i class="fas fa-sync-alt"></i> Reset Filters
            </a>
        </div>
    <?php endif; ?>
    
    

<?php 
$stmt->close();
$conn->close();
include 'footer.php'; 
?>
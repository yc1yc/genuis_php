<?php
require_once __DIR__ . '/../../includes/auth.php';
$user = requireAuth('admin');

// Déterminer la page active
$adminPage = $_GET['admin_page'] ?? 'index';
?>

<div class="admin-container">
    <div class="admin-sidebar">
        <nav class="admin-nav">
            <a href="?page=admin&admin_page=dashboard" class="admin-nav-item <?php echo $adminPage === 'dashboard' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de bord</span>
            </a>

            <a href="?page=admin&admin_page=users" class="admin-nav-item <?php echo $adminPage === 'users' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Utilisateurs</span>
            </a>

            <a href="?page=admin&admin_page=vehicles" class="admin-nav-item <?php echo $adminPage === 'vehicles' ? 'active' : ''; ?>">
                <i class="fas fa-car"></i>
                <span>Véhicules</span>
            </a>

            <a href="?page=admin&admin_page=reservations" class="admin-nav-item <?php echo $adminPage === 'reservations' ? 'active' : ''; ?>">
                <i class="fas fa-calendar-check"></i>
                <span>Réservations</span>
            </a>

            <a href="?page=admin&admin_page=settings" class="admin-nav-item <?php echo $adminPage === 'settings' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Paramètres</span>
            </a>

            <a href="?page=admin&admin_page=reports" class="admin-nav-item <?php echo $adminPage === 'reports' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Rapports</span>
            </a>
        </nav>
    </div>

    <div class="admin-content">
        <?php
        // Gérer les différentes pages d'administration
        switch ($adminPage) {
            case 'vehicles':
                $action = $_GET['action'] ?? 'list';
                switch ($action) {
                    case 'add':
                    case 'edit':
                        require_once __DIR__ . '/vehicle_form.php';
                        break;
                    default:
                        require_once __DIR__ . '/vehicles.php';
                        break;
                }
                break;

            case 'index':
            default:
        ?>
            <div class="admin-header">
                <h1>Administration</h1>
            </div>

            <div class="admin-menu">
                <a href="?page=admin&admin_page=dashboard" class="admin-card">
                    <i class="fas fa-tachometer-alt"></i>
                    <h3>Tableau de bord</h3>
                    <p>Statistiques et aperçu général</p>
                </a>

                <a href="?page=admin&admin_page=users" class="admin-card">
                    <i class="fas fa-users"></i>
                    <h3>Utilisateurs</h3>
                    <p>Gestion des comptes</p>
                </a>

                <a href="?page=admin&admin_page=vehicles" class="admin-card">
                    <i class="fas fa-car"></i>
                    <h3>Véhicules</h3>
                    <p>Gestion de la flotte</p>
                </a>

                <a href="?page=admin&admin_page=reservations" class="admin-card">
                    <i class="fas fa-calendar-check"></i>
                    <h3>Réservations</h3>
                    <p>Suivi des locations</p>
                </a>

                <a href="?page=admin&admin_page=settings" class="admin-card">
                    <i class="fas fa-cog"></i>
                    <h3>Paramètres</h3>
                    <p>Configuration du site</p>
                </a>

                <a href="?page=admin&admin_page=reports" class="admin-card">
                    <i class="fas fa-chart-bar"></i>
                    <h3>Rapports</h3>
                    <p>Statistiques détaillées</p>
                </a>
            </div>
        <?php
            break;
        }
        ?>
    </div>
</div>

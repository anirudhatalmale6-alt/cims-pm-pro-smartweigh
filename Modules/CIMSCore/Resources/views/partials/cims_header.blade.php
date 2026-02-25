{{--
    CIMS Header Partial
    Independent header component - matches CIMS menu design (teal gradient, flat, clean)
--}}

<div class="cims-header">
    <div class="cims-header-inner">

        <!-- Left: Logo + Brand -->
        <div class="cims-header-left">
            <div class="cims-header-brand">
                <img src="{{ asset('assets/images/cims_core/atp_cims_logo.jpg') }}" alt="SmartDash CIMS" class="cims-header-logo">
                <span class="cims-header-separator"></span>
                <span class="cims-header-brand-name">SmartDash - CIMS 3000</span>
            </div>
        </div>

        <!-- Right: Search & Actions -->
        <div class="cims-header-right">
            <!-- Search Box -->
            <div class="cims-search-box">
                <input type="text" class="cims-search-input" placeholder="Search here...">
                <i class="fas fa-search cims-search-icon"></i>
            </div>

            <!-- Action Icons -->
            <div class="cims-header-actions">
                <!-- Favorites -->
                <div class="cims-header-action" title="Favorites">
                    <i class="fas fa-star"></i>
                    <span class="cims-badge cims-badge-warning">7</span>
                </div>

                <!-- Notifications -->
                <div class="cims-header-action" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="cims-badge cims-badge-danger">12</span>
                </div>

                <!-- Messages -->
                <div class="cims-header-action" title="Messages">
                    <i class="fas fa-envelope"></i>
                    <span class="cims-badge cims-badge-primary">8</span>
                </div>

                <!-- Tasks -->
                <div class="cims-header-action" title="Tasks">
                    <i class="fas fa-list-check"></i>
                    <span class="cims-badge cims-badge-success">3</span>
                </div>
            </div>

            <!-- User Profile -->
            <div class="cims-header-profile">
                <img src="{{ asset('assets/images/cims_core/user_profile.jpg') }}" alt="User" class="cims-profile-img">
            </div>
        </div>

    </div>
</div>

<style>
/* =============================================
   CIMS HEADER STYLES
   Clean flat design with teal accents
============================================= */

.cims-header {
    background: #f8f9fa !important;
    border: none !important;
    border-top: none !important;
    border-bottom: 3px solid #17A2B8 !important;
    padding: 12px 25px !important;
    position: sticky;
    top: 0;
    z-index: 999;
}

/* Override Fillow red border */
.header, .cims-header, [class*="header"] {
    border-top: none !important;
}

.cims-header-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

/* Left: Brand + Page Title */
.cims-header-left {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Brand Section */
.cims-header-brand {
    display: flex;
    align-items: center;
    gap: 15px;
}

.cims-header-logo {
    height: 50px;
    width: auto;
    object-fit: contain;
    border: none;
}

.cims-header-separator {
    width: 2px;
    height: 40px;
    background: #17A2B8;
}

.cims-header-brand-name {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0d3d56;
    white-space: nowrap;
}

/* Right: Search & Actions */
.cims-header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

/* Search Box */
.cims-search-box {
    position: relative;
    display: flex;
    align-items: center;
}

.cims-search-input {
    width: 220px;
    padding: 10px 40px 10px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    background: #fff;
    color: #333;
    font-size: 0.9rem;
    transition: all 0.25s ease;
}

.cims-search-input::placeholder {
    color: #999;
}

.cims-search-input:focus {
    outline: none;
    border-color: #17A2B8;
    box-shadow: 0 0 0 3px rgba(23, 162, 184, 0.15);
}

.cims-search-icon {
    position: absolute;
    right: 15px;
    color: #999;
    font-size: 0.9rem;
}

/* Action Icons */
.cims-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.cims-header-action {
    position: relative;
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #555;
    cursor: pointer;
    border-radius: 50%;
    background: #fff;
    border: 1px solid #e0e0e0;
    transition: all 0.25s ease;
}

.cims-header-action:hover {
    background: #17A2B8;
    border-color: #17A2B8;
    color: #fff;
}

.cims-header-action i {
    font-size: 1rem;
}

/* Badges */
.cims-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    min-width: 20px;
    height: 20px;
    padding: 0 6px;
    font-size: 0.65rem;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
}

.cims-badge-primary { background: #17A2B8; }
.cims-badge-success { background: #20c997; }
.cims-badge-warning { background: #ffc107; color: #333; }
.cims-badge-danger { background: #dc3545; }

/* User Profile */
.cims-header-profile {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid #17A2B8;
    cursor: pointer;
    transition: all 0.25s ease;
}

.cims-header-profile:hover {
    border-color: #0d3d56;
    transform: scale(1.05);
}

.cims-profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Responsive */
@media (max-width: 992px) {
    .cims-search-box {
        display: none;
    }

    .cims-header-brand-name {
        font-size: 1.1rem;
    }

    .cims-header-logo {
        height: 42px;
    }
}

@media (max-width: 576px) {
    .cims-header {
        padding: 10px 15px !important;
    }

    .cims-header-actions {
        gap: 5px;
    }

    .cims-header-action {
        width: 36px;
        height: 36px;
    }

    .cims-header-logo {
        height: 36px;
    }

    .cims-header-brand-name {
        font-size: 0.95rem;
    }
}
</style>

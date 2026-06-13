<?php
// team.php
// Secure, corrected version of your team listing with role filter + district-tolerance

declare(strict_types=1);

include 'conn.php'; // expects $conn as mysqli connection

// --- sanitize incoming filter
$filter = isset($_GET['type']) ? trim((string)$_GET['type']) : '';

// normalize filter for display and comparisons
$filter_normalized = mb_strtolower($filter);

// tolerant mapping for common typo 'Dristic' -> 'District'
$use_like = false;
$like_value = '';
$dbFilter = null;

if ($filter_normalized !== '') {
    if (strpos($filter_normalized, 'drist') !== false || strpos($filter_normalized, 'district') !== false) {
        $use_like = true;
        $like_value = '%district%';
    } else {
        // optional: restrict to known role labels to avoid arbitrary values
        $allowed_roles = [
            'state node' => 'State Node',
            'zonal manager' => 'Zonal Manager',
            'block co-ordinator' => 'Block Co-Ordinator',
            'dristic co-ordinator' => 'Dristic Co-Ordinator', // accept original label too
        ];
        // try to map a user-friendly label (case-insensitive)
        $found = false;
        foreach ($allowed_roles as $k => $label) {
            if ($filter_normalized === $k || $filter_normalized === mb_strtolower($label)) {
                $dbFilter = $label;
                $found = true;
                break;
            }
        }
        if (! $found) {
            // fallback: use raw filter as-is (but still prepared)
            $dbFilter = $filter;
        }
    }
}

// --- prepare SQL query with safe prepared statements
// We'll always select a consistent set of columns
$sql = '';
$stmt = null;

if ($filter !== '' && $use_like) {
    $sql = "SELECT m.owner_name, m.photo, m.member_type, d.name AS district
            FROM members m
            LEFT JOIN districts d ON d.id = m.district
            WHERE LOWER(m.member_type) LIKE ?
            ORDER BY m.id DESC";
    $stmt = $conn->prepare($sql);
    $param = mb_strtolower($like_value);
    $stmt->bind_param('s', $param);
} elseif ($filter !== '' && $dbFilter !== null) {
    $sql = "SELECT m.owner_name, m.photo, m.member_type, d.name AS district
            FROM members m
            LEFT JOIN districts d ON d.id = m.district
            WHERE m.member_type = ?
            ORDER BY m.id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $dbFilter);
} else {
    // no filter: list latest 100
    $sql = "SELECT m.owner_name, m.photo, m.member_type, d.name AS district
            FROM members m
            LEFT JOIN districts d ON d.id = m.district
            ORDER BY m.id DESC
            LIMIT 100";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$team_result = $stmt->get_result();

// helper to build link and determine active class (for use in the HTML below)
function role_link(string $label, string $current_filter): string {
    $url = '?type=' . rawurlencode($label);
    $is_active = (mb_strtolower(trim($current_filter)) === mb_strtolower(trim($label)));
    $active_class = $is_active ? ' active' : '';
    // aria-selected must be 'true' or 'false'
    $aria_selected = $is_active ? 'true' : 'false';
    // use htmlspecialchars for label output
    return '<a role="tab" aria-selected="'. $aria_selected .'" class="role-btn'. $active_class .'" href="'. $url .'">'. htmlspecialchars($label, ENT_QUOTES, 'UTF-8') .'</a>';
}

// Course Data Structure
$courses = [
    'diploma' => [
        'id' => 'diploma',
        'title' => 'Diploma Programs',
        'duration_text' => '12 Months',
        'icon' => 'fa-graduation-cap',
        'description' => 'Comprehensive diploma programs for complete professional development',
        'color' => '#2c3e50',
        'bg_light' => '#ecf0f1',
        'courses' => [
            [
                'code' => 'SIET-01',
                'name' => 'ADCA',
                'full_name' => 'ADVANCE DIPLOMA IN COMPUTER APPLICATION',
                'duration' => '12 Months',
                'fee_range' => '₹5,000 - ₹8,500'
            ],
            [
                'code' => 'SIET-02',
                'name' => 'ADCA Plus',
                'full_name' => 'ADVANCE DIPLOMA IN COMPUTER APPLICATION Plus',
                'duration' => '12 Months',
                'fee_range' => '₹5,000 - ₹8,500'
            ],
            [
                'code' => 'SIET-03',
                'name' => 'CTTP',
                'full_name' => 'COMPUTER TEACHER TRAINING PROGRAMME',
                'duration' => '12 Months',
                'fee_range' => '₹5,000 - ₹8,500'
            ],
            [
                'code' => 'SIET-04',
                'name' => 'ADSE',
                'full_name' => 'Advance Diploma in Software Engineering',
                'duration' => '12 Months',
                'fee_range' => '₹5,000 - ₹8,500'
            ],
            [
                'code' => 'SIET-05',
                'name' => 'DHT',
                'full_name' => 'Diploma in Hardware Networking',
                'duration' => '12 Months',
                'fee_range' => '₹5,000 - ₹8,500'
            ],
            [
                'code' => 'SIET-29',
                'name' => 'PGDCA',
                'full_name' => 'Post Graduate Diploma in Computer Application',
                'duration' => '12 Months',
                'fee_range' => '₹6,500 - ₹8,500'
            ]
        ]
    ],
    'design' => [
        'id' => 'design',
        'title' => 'Design Programs',
        'duration_text' => '9 Months',
        'icon' => 'fa-paint-brush',
        'description' => 'Creative design courses for aspiring designers',
        'color' => '#e67e22',
        'bg_light' => '#fef5e7',
        'courses' => [
            [
                'code' => 'SIET-06',
                'name' => 'DID',
                'full_name' => 'Diploma in Designing',
                'duration' => '9 Months',
                'fee_range' => '₹5,000 - ₹7,500'
            ]
        ]
    ],
    'certificate' => [
        'id' => 'certificate',
        'title' => 'Certificate Programs',
        'duration_text' => '6 Months',
        'icon' => 'fa-certificate',
        'description' => 'Focused certification courses for skill development',
        'color' => '#27ae60',
        'bg_light' => '#e8f8f0',
        'courses' => [
            [
                'code' => 'SIET-07',
                'name' => 'DTP',
                'full_name' => 'DESK TOP PUBLISHING',
                'duration' => '6 Months',
                'fee_range' => '₹3,000 - ₹4,500'
            ],
            [
                'code' => 'SIET-08',
                'name' => 'DCA',
                'full_name' => 'DIPLOMA IN COMPUTER APPLICATION',
                'duration' => '6 Months',
                'fee_range' => '₹3,000 - ₹4,500'
            ],
            [
                'code' => 'SIET-09',
                'name' => 'DCAPlus',
                'full_name' => 'DIPLOMA IN COMPUTER APPLICATION Plus',
                'duration' => '6 Months',
                'fee_range' => '₹3,000 - ₹4,500'
            ],
            [
                'code' => 'SIET-17',
                'name' => 'H&ET',
                'full_name' => 'Hindi & English Typing',
                'duration' => '6 Months',
                'fee_range' => '₹3,000 - ₹4,500'
            ],
            [
                'code' => 'SIET-20',
                'name' => 'CA',
                'full_name' => 'Certificate in Accounting',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹4,500'
            ],
            [
                'code' => 'SIET-21',
                'name' => 'CWD',
                'full_name' => 'Certificate in Web Designing',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹4,500'
            ],
            [
                'code' => 'SIET-22',
                'name' => 'CS',
                'full_name' => 'Certificate in Stenography',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-23',
                'name' => 'CECE',
                'full_name' => 'Certificate in Early Childhood Education',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-24',
                'name' => 'CF',
                'full_name' => 'Certificate in Finance',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-25',
                'name' => 'CCSE',
                'full_name' => 'Certificate Course in Spoken English',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-26',
                'name' => 'CCA',
                'full_name' => 'Certificate In Computer Application',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-27',
                'name' => 'CHN',
                'full_name' => 'Certificate in Hardware & Networking',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ],
            [
                'code' => 'SIET-28',
                'name' => 'CMA',
                'full_name' => 'Certificate in Multimedia & Animation',
                'duration' => '6 Months',
                'fee_range' => '₹2,500 - ₹5,500'
            ]
        ]
    ],
    'short_term' => [
        'id' => 'short_term',
        'title' => 'Short Term Programs',
        'duration_text' => '3 Months',
        'icon' => 'fa-clock',
        'description' => 'Quick skill enhancement courses for immediate career growth',
        'color' => '#e74c3c',
        'bg_light' => '#fdedea',
        'courses' => [
            [
                'code' => 'SIET-10',
                'name' => 'Tally',
                'full_name' => 'TRANSACTION ALLOWED LINEAR IN LINE YARD',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-12',
                'name' => 'BCC',
                'full_name' => 'BASIC COMPUTER COURSE',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-13',
                'name' => 'SG',
                'full_name' => 'Stenographer',
                'duration' => '3 Months',
                'fee_range' => '₹2,500 - ₹4,500'
            ],
            [
                'code' => 'SIET-15',
                'name' => 'ET',
                'full_name' => 'English Typing',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-16',
                'name' => 'HT',
                'full_name' => 'Hindi Typing',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-18',
                'name' => 'S.H.H.T',
                'full_name' => 'Short Hand Hindi Typing',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-19',
                'name' => 'S.H.E.T',
                'full_name' => 'Short Hand English Typing',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ],
            [
                'code' => 'SIET-11',
                'name' => 'SE',
                'full_name' => 'SPOKEN ENGLISH',
                'duration' => '3 Months',
                'fee_range' => '₹1,500 - ₹2,500'
            ]
        ]
    ]
];

// Get active tab from URL parameter (default: diploma)
$active_tab = isset($_GET['course_tab']) ? $_GET['course_tab'] : 'diploma';
if (!array_key_exists($active_tab, $courses)) {
    $active_tab = 'diploma';
}

$uploads_dir = __DIR__ . '/admin/uploads/'; // adjust if your uploads directory is elsewhere
?>

<!DOCTYPE html>
<html dir="ltr" lang="en">
<head>

<!-- Meta Tags -->
<meta name="viewport" content="width=device-width,initial-scale=1.0"/>
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/>
<meta name="description" content="Sharnay Institute of Education Technology Pvt. Ltd." />
<meta name="keywords" content="academy, course, education, education html theme, elearning, learning," />
<meta name="author" content="ThemeMascot" />

<!-- Page Title -->
<title>Sharnay Institute of Education Technology</title>

<!-- Favicon and Touch Icons -->
<link href="images/favicon.png" rel="shortcut icon" type="image/png">
<link href="images/apple-touch-icon.png" rel="apple-touch-icon">
<link href="images/apple-touch-icon-72x72.png" rel="apple-touch-icon" sizes="72x72">
<link href="images/apple-touch-icon-114x114.png" rel="apple-touch-icon" sizes="114x114">
<link href="images/apple-touch-icon-144x144.png" rel="apple-touch-icon" sizes="144x144">

<!-- Font Awesome 6 (Free) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<!-- Stylesheet -->
<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
<link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css">
<link href="css/animate.css" rel="stylesheet" type="text/css">
<link href="css/css-plugin-collections.css" rel="stylesheet"/>
<!-- CSS | menuzord megamenu skins -->
<link id="menuzord-menu-skins" href="css/menuzord-skins/menuzord-rounded-boxed.css" rel="stylesheet"/>
<!-- CSS | Main style file -->
<link href="css/style-main.css" rel="stylesheet" type="text/css">
<!-- CSS | Preloader Styles -->
<link href="css/preloader.css" rel="stylesheet" type="text/css">
<!-- CSS | Custom Margin Padding Collection -->
<link href="css/custom-bootstrap-margin-padding.css" rel="stylesheet" type="text/css">
<!-- CSS | Responsive media queries -->
<link href="css/responsive.css" rel="stylesheet" type="text/css">

<!-- Revolution Slider 5.x CSS settings -->
<link  href="js/revolution-slider/css/settings.css" rel="stylesheet" type="text/css"/>
<link  href="js/revolution-slider/css/layers.css" rel="stylesheet" type="text/css"/>
<link  href="js/revolution-slider/css/navigation.css" rel="stylesheet" type="text/css"/>

<!-- CSS | Theme Color -->
<link href="css/colors/theme-skin-color-set-1.css" rel="stylesheet" type="text/css">

<!-- external javascripts -->
<script src="js/jquery-2.2.4.min.js"></script>
<script src="js/jquery-ui.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<!-- JS | jquery plugin collection for this theme -->
<script src="js/jquery-plugin-collection.js"></script>

<!-- Revolution Slider 5.x SCRIPTS -->
<script src="js/revolution-slider/js/jquery.themepunch.tools.min.js"></script>
<script src="js/revolution-slider/js/jquery.themepunch.revolution.min.js"></script>

<style>
/* ===== Our Team — Final "OSM" Design ===== */

.our-team{
  position: relative;
  overflow: hidden;
  color: #071029;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  padding: 56px 18px;
}

/* animated gradient background */
.our-team .bg-gradient {
  position: absolute;
  inset: 0;
  z-index: 0;
  pointer-events: none;
  background: linear-gradient(120deg, #eaf6ff 0%, #f7f9ff 25%, #e8f3ff 50%, #fff9f6 75%, #f3f7ff 100%);
  mix-blend-mode: normal;
  opacity: 1;
  filter: saturate(1.02);
  animation: gradientShift 14s ease-in-out infinite;
}

/* floating colorful blobs */
.our-team .bg-blob {
  position: absolute;
  width: 420px;
  height: 420px;
  border-radius: 50%;
  filter: blur(48px) saturate(1.05);
  opacity: 0.16;
  z-index: 0;
  pointer-events: none;
  transform: translate3d(0,0,0);
}
.our-team .bg-blob.b1 {
  right: -120px;
  top: -80px;
  background: radial-gradient(circle at 20% 30%, #01a3ff, transparent 35%),
              linear-gradient(120deg, rgba(1,163,255,0.9), rgba(2,132,199,0.6));
  animation: blobFloat 18s ease-in-out infinite;
}
.our-team .bg-blob.b2 {
  left: -160px;
  bottom: -120px;
  width: 520px; height: 520px;
  background: radial-gradient(circle at 70% 80%, #ffd59e, transparent 30%),
              linear-gradient(120deg, rgba(255,183,77,0.9), rgba(255,132,99,0.6));
  animation: blobFloat 22s ease-in-out infinite reverse;
  opacity: 0.12;
}

/* subtle vignette over everything for focus */
.our-team .bg-vignette {
  position: absolute;
  inset: 0;
  z-index: 1;
  background: radial-gradient(ellipse at center, rgba(255,255,255,0) 20%, rgba(9,18,32,0.03) 80%);
  pointer-events: none;
}

/* ensure content is above background layers */
.our-team .container { position: relative; z-index: 2; max-width:1200px; margin:0 auto; }

/* title */
.our-team .section-title { text-align: center; margin-bottom: 2rem; }
.our-team .section-title h2 {
  font-size: 2rem;
  margin: 0 0 6px;
  color: #041025;
  font-weight: 800;
}
.our-team .section-title .text-muted {
  color: #58606a;
  font-size: 0.95rem;
}

/* role buttons row */
.role-row {
  display:flex;
  gap:12px;
  justify-content:center;
  flex-wrap:wrap;
  margin-bottom:20px;
  z-index:2;
}
.role-btn {
  padding:10px 14px;
  border-radius:10px;
  background:#071029;
  color:#fff;
  text-transform:uppercase;
  font-weight:700;
  font-size:0.82rem;
  text-decoration:none;
  box-shadow: 0 6px 18px rgba(7,16,36,0.06);
  transition: transform .18s, box-shadow .18s, background .18s;
}
.role-btn:hover { transform: translateY(-3px); background:#063158; }
.role-btn.active { background:#ffb300; color:#061018; box-shadow: 0 12px 28px rgba(4,16,30,0.12); }

/* grid layout */
.our-team .team-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 1.25rem;
  align-items: stretch;
  z-index: 2;
}

/* card: glass + soft border + inner accent */
.team-card {
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 1.25rem;
  gap: 0.45rem;
  border-radius: 16px;
  min-height: 260px;
  background: linear-gradient(180deg, rgba(255,255,255,0.76), rgba(255,255,255,0.58));
  border: 1px solid rgba(7,16,36,0.05);
  backdrop-filter: blur(8px) saturate(1.05);
  -webkit-backdrop-filter: blur(8px) saturate(1.05);
  box-shadow: 0 10px 30px rgba(4,16,30,0.08);
  transition: transform 360ms cubic-bezier(.2,.9,.3,1), box-shadow 360ms;
  overflow: visible;
  transform-style: preserve-3d;
  z-index: 2;
  opacity: 0;
  transform: translateY(18px) scale(0.996);
  animation: cardPop 760ms forwards;
}

/* subtle top accent line inside card */
.team-card::after {
  content: "";
  position: absolute;
  left: 14%;
  right: 14%;
  top: 12px;
  height: 6px;
  border-radius: 6px;
  background: linear-gradient(90deg, rgba(1,163,255,0.22), rgba(255,183,77,0.18));
  z-index: 1;
  opacity: 0.7;
  filter: blur(6px);
  pointer-events: none;
}

/* staggered entrance delays */
.team-grid .team-card:nth-child(1){ animation-delay:.04s; }
.team-grid .team-card:nth-child(2){ animation-delay:.10s; }
.team-grid .team-card:nth-child(3){ animation-delay:.16s; }
.team-grid .team-card:nth-child(4){ animation-delay:.22s; }
.team-grid .team-card:nth-child(5){ animation-delay:.28s; }
.team-grid .team-card:nth-child(6){ animation-delay:.34s; }

/* avatar with ring and shine */
.team-card .avatar {
  width: 118px;
  height: 118px;
  border-radius: 50%;
  object-fit: cover;
  position: relative;
  z-index: 3;
  border: 6px solid rgba(255,255,255,0.95);
  box-shadow: 0 14px 34px rgba(2,6,23,0.10);
  transition: transform 400ms cubic-bezier(.2,.9,.3,1), box-shadow 400ms;
  background: linear-gradient(180deg,#fff,#f6fbff);
}

/* name and role layout */
.team-card .name {
  font-size: 1.06rem;
  font-weight: 700;
  color: #041025;
  margin-top: 6px;
  z-index: 3;
  text-align: center;
}
.team-card .role {
  display: inline-block;
  font-size: 0.86rem;
  color: #044f66;
  padding: 8px 12px;
  border-radius: 999px;
  background: linear-gradient(90deg, rgba(1,163,255,0.08), rgba(255,183,77,0.06));
  border: 1px solid rgba(1,163,255,0.06);
  margin-top: 6px;
  z-index: 3;
}
.team-card .district {
  font-size: 0.75rem;
  color: #7f8c8d;
  margin-top: 4px;
}

/* 3D hover transform + avatar pop */
.team-card:hover {
  transform: translateY(-16px) rotateX(4deg) rotateY(2deg) scale(1.02);
  box-shadow: 0 36px 72px rgba(4,16,30,0.18);
}
.team-card:hover .avatar {
  transform: translateY(-8px) scale(1.04);
  box-shadow: 0 26px 56px rgba(2,6,23,0.16);
}

/* keyboard focus */
.team-card:focus-within, .team-card:focus {
  outline: 3px solid rgba(1,163,255,0.12);
  outline-offset: 6px;
}

/* responsive adjustments */
@media (max-width: 575px){
  .our-team { padding: 2.6rem 0; }
  .team-card { padding: 1rem; min-height: 220px; border-radius: 12px; }
  .team-card .avatar { width: 96px; height: 96px; border-width: 5px; }
  .team-card .name { font-size: 1rem; }
  .our-team .bg-blob { display: none; }
}

/* accessibility: reduce motion */
@media (prefers-reduced-motion: reduce) {
  .our-team .bg-blob, .our-team .bg-gradient, .team-card { animation: none !important; transition: none !important; }
}

/* ---- Keyframes ---- */
@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; transform: scale(1.01); }
  100% { background-position: 0% 50%; transform: scale(1); }
}
@keyframes blobFloat {
  0% { transform: translateY(0) rotate(0deg) scale(1); opacity: 0.16; }
  25% { transform: translateY(-6px) rotate(8deg) scale(1.02); opacity: 0.18; }
  50% { transform: translateY(0) rotate(0deg) scale(0.98); opacity: 0.14; }
  75% { transform: translateY(6px) rotate(-6deg) scale(1.01); opacity: 0.17; }
  100% { transform: translateY(0) rotate(0deg) scale(1); opacity: 0.16; }
}
@keyframes cardPop {
  0% { opacity: 0; transform: translateY(22px) scale(.996); }
  60% { opacity: 1; transform: translateY(-6px) scale(1.003); }
  100% { opacity: 1; transform: translateY(0) scale(1); }
}

/* ===== Enhanced Courses Section with Tabs ===== */
.courses-tab-section {
    padding: 80px 0;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    position: relative;
    overflow: hidden;
}

.courses-tab-section:before {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    background: radial-gradient(circle, rgba(255,107,53,0.03) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.courses-tab-section:after {
    content: '';
    position: absolute;
    bottom: -80px;
    left: -80px;
    width: 300px;
    height: 300px;
    background: radial-gradient(circle, rgba(255,107,53,0.03) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.section-header {
    text-align: center;
    margin-bottom: 50px;
    position: relative;
}

.section-header h2 {
    font-size: 2.8rem;
    margin-bottom: 15px;
    color: #1a2a3a;
    font-weight: 700;
    position: relative;
    display: inline-block;
}

.section-header h2 span {
    color: #ff6b35;
    position: relative;
}

.section-header h2 span:after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, #ff6b35, #ffa559);
    border-radius: 3px;
}

.section-header p {
    color: #6c757d;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

/* Tab Navigation */
.course-tabs-nav {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 40px;
    position: relative;
    z-index: 2;
}

.tab-btn {
    padding: 14px 32px;
    border: none;
    background: white;
    border-radius: 50px;
    font-size: 1rem;
    font-weight: 600;
    color: #4a5568;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1px solid #e9ecef;
}

.tab-btn i {
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.tab-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #ff6b35;
    color: #ff6b35;
}

.tab-btn.active {
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    border-color: transparent;
    box-shadow: 0 10px 30px rgba(255,107,53,0.3);
}

.tab-btn.active i {
    color: white;
}

/* Tab Content */
.tab-content {
    display: none;
    animation: fadeInUp 0.5s ease forwards;
}

.tab-content.active {
    display: block;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Category Header within Tab */
.category-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
}

.category-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    box-shadow: 0 10px 30px rgba(255,107,53,0.2);
}

.category-icon i {
    font-size: 40px;
    color: white;
}

.category-header h3 {
    font-size: 2rem;
    color: #1a2a3a;
    margin-bottom: 10px;
    font-weight: 700;
}

.category-header .duration-badge {
    display: inline-block;
    background: #e9ecef;
    padding: 6px 16px;
    border-radius: 30px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #ff6b35;
    margin-bottom: 15px;
}

.category-header p {
    color: #6c757d;
    font-size: 1rem;
    max-width: 500px;
    margin: 0 auto;
}

/* Course Grid */
.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 30px;
}

.course-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(0,0,0,0.05);
}

.course-card:before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #ff6b35, #ffa559);
    transform: scaleX(0);
    transition: transform 0.4s ease;
}

.course-card:hover:before {
    transform: scaleX(1);
}

.course-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12);
}

.course-badge {
    position: absolute;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    padding: 5px 14px;
    border-radius: 30px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}

.course-name {
    font-size: 1.6rem;
    font-weight: 800;
    color: #1a2a3a;
    margin-bottom: 8px;
    letter-spacing: -0.5px;
}

.course-fullname {
    color: #6c757d;
    font-size: 0.85rem;
    margin-bottom: 20px;
    line-height: 1.5;
    min-height: 50px;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    padding: 15px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.duration, .fee {
    font-size: 0.9rem;
    color: #4a5568;
    font-weight: 500;
}

.duration i, .fee i {
    margin-right: 8px;
    color: #ff6b35;
}

.enquire-btn {
    width: 100%;
    background: transparent;
    border: 2px solid #ff6b35;
    color: #ff6b35;
    padding: 12px;
    border-radius: 50px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.enquire-btn:hover {
    background: linear-gradient(135deg, #ff6b35, #ff8c42);
    color: white;
    border-color: transparent;
    transform: translateY(-2px);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px;
    background: white;
    border-radius: 20px;
    grid-column: 1 / -1;
}

.empty-state i {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state p {
    color: #6c757d;
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 768px) {
    .courses-tab-section {
        padding: 50px 0;
    }
    
    .section-header h2 {
        font-size: 2rem;
    }
    
    .tab-btn {
        padding: 10px 20px;
        font-size: 0.85rem;
    }
    
    .tab-btn i {
        font-size: 1rem;
    }
    
    .course-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .course-card {
        padding: 20px;
    }
    
    .course-name {
        font-size: 1.3rem;
    }
    
    .category-header h3 {
        font-size: 1.5rem;
    }
}
</style>

</head>
<body class="">
<div id="wrapper" class="clearfix">
 
  <!-- Header -->
 <?php include 'head.php'; ?>

  <!-- Start main-content -->
  <div class="main-content">
  
    <!-- Section: home -->
    <section id="home">
      <div class="container-fluid p-0">
        
        <!-- Slider Revolution Start -->
        <div class="rev_slider_wrapper">
          <div class="rev_slider" data-version="5.0">
            <ul>

              <!-- SLIDE 1 -->
              <li data-index="rs-1" data-transition="slidingoverlayhorizontal" data-slotamount="default" data-easein="default" data-easeout="default" data-masterspeed="default" data-thumb="images/s3.jpg" data-rotate="0" data-saveperformance="off" data-title="Slide 1" data-description="">
                <!-- MAIN IMAGE -->
                <img src="images/s3.jpg"  alt=""  data-bgposition="center 10%" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-bgparallax="10" data-no-retina>
                <!-- LAYERS -->

                <!-- LAYER NR. 1 -->
                <div class="tp-caption tp-resizeme text-uppercase text-white font-raleway"
                  id="rs-1-layer-1"

                  data-x="['left']"
                  data-hoffset="['30']"
                  data-y="['middle']"
                  data-voffset="['-110']" 
                  data-fontsize="['100']"
                  data-lineheight="['110']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:700;">Sharnay Institute
                </div>

                <!-- LAYER NR. 2 -->
                <div class="tp-caption tp-resizeme text-uppercase text-white font-raleway bg-theme-colored-transparent border-left-theme-color-2-6px pl-20 pr-20"
                  id="rs-1-layer-2"

                  data-x="['left']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['-25']" 
                  data-fontsize="['35']"
                  data-lineheight="['54']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:600;">Sharnay Institute Of Education and Technology Pvt. Ltd.
                </div>

                <!-- LAYER NR. 3 -->
                <div class="tp-caption tp-resizeme text-white" 
                  id="rs-1-layer-3"

                  data-x="['left']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['35']"
                  data-fontsize="['16']"
                  data-lineheight="['28']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:0px; font-weight:400;">We provides always our best education for aur students and  always<br> try to achieve our students trust and satisfaction.
                </div>

                <!-- LAYER NR. 4 -->
                <div class="tp-caption tp-resizeme" 
                  id="rs-1-layer-4"

                  data-x="['left']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['100']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;"
                  data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;" 
                  data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;" 
                  data-mask_in="x:0px;y:[100%];s:inherit;e:inherit;" 
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:1px;"><a class="btn btn-colored btn-lg btn-flat btn-theme-colored border-left-theme-color-2-6px pl-20 pr-20" href="course.php">View Details</a> 
                </div>
              </li>

              <!-- SLIDE 2 -->
              <li data-index="rs-2" data-transition="slidingoverlayhorizontal" data-slotamount="default" data-easein="default" data-easeout="default" data-masterspeed="default" data-thumb="images/s2.jpg" data-rotate="0" data-saveperformance="off" data-title="Slide 2" data-description="">
                <!-- MAIN IMAGE -->
                <img src="images/s2.jpg"  alt=""  data-bgposition="center 40%" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-bgparallax="10" data-no-retina>
                <!-- LAYERS -->

                <!-- LAYER NR. 1 -->
                <div class="tp-caption tp-resizeme text-uppercase  bg-dark-transparent-5 text-white font-raleway border-left-theme-color-2-6px border-right-theme-color-2-6px pl-30 pr-30"
                  id="rs-2-layer-1"
                
                  data-x="['center']"
                  data-hoffset="['0']"
                  data-y="['middle']"
                  data-voffset="['-90']" 
                  data-fontsize="['28']"
                  data-lineheight="['54']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:400; border-radius: 30px;">Feed Your Knowledge 
                </div>

                <!-- LAYER NR. 2 -->
                <div class="tp-caption tp-resizeme text-uppercase bg-theme-colored-transparent text-white font-raleway pl-30 pr-30"
                  id="rs-2-layer-2"

                  data-x="['center']"
                  data-hoffset="['0']"
                  data-y="['middle']"
                  data-voffset="['-20']"
                  data-fontsize="['48']"
                  data-lineheight="['70']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:700; border-radius: 30px;">India's Best Institute
                </div>

                <!-- LAYER NR. 3 -->
                <div class="tp-caption tp-resizeme text-white text-center" 
                  id="rs-2-layer-3"

                  data-x="['center']"
                  data-hoffset="['0']"
                  data-y="['middle']"
                  data-voffset="['50']"
                  data-fontsize="['16']"
                  data-lineheight="['28']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:0px; font-weight:400;">We provides always our best Education For Students and  always<br> try to achieve our students trust and satisfaction.
                </div>

                <!-- LAYER NR. 4 -->
                <div class="tp-caption tp-resizeme" 
                  id="rs-2-layer-4"

                  data-x="['center']"
                  data-hoffset="['0']"
                  data-y="['middle']"
                  data-voffset="['115']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;"
                  data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;" 
                  data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;" 
                  data-mask_in="x:0px;y:[100%];s:inherit;e:inherit;" 
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:1px;"><a class="btn btn-default btn-circled btn-transparent pl-20 pr-20" href="student_reg.php">Apply Now</a> 
                </div>
              </li>

              <!-- SLIDE 3 -->
              <li data-index="rs-3" data-transition="slidingoverlayhorizontal" data-slotamount="default" data-easein="default" data-easeout="default" data-masterspeed="default" data-thumb="images/s1.jpg" data-rotate="0" data-saveperformance="off" data-title="Slide 3" data-description="">
                <!-- MAIN IMAGE -->
                <img src="images/s1.jpg"  alt=""  data-bgposition="center center" data-bgfit="cover" data-bgrepeat="no-repeat" class="rev-slidebg" data-bgparallax="10" data-no-retina>
                <!-- LAYERS -->

                <!-- LAYER NR. 1 -->
                <div class="tp-caption tp-resizeme text-uppercase text-white font-raleway bg-theme-colored-transparent border-right-theme-color-2-6px pr-20 pl-20"
                  id="rs-3-layer-1"

                  data-x="['right']"
                  data-hoffset="['30']"
                  data-y="['middle']"
                  data-voffset="['-90']" 
                  data-fontsize="['64']"
                  data-lineheight="['72']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:600;">Best Institute
                </div>

                <!-- LAYER NR. 2 -->
                <div class="tp-caption tp-resizeme text-uppercase bg-dark-transparent-6 text-white font-raleway pl-20 pr-20"
                  id="rs-3-layer-2"

                  data-x="['right']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['-25']" 
                  data-fontsize="['32']"
                  data-lineheight="['54']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1000" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 7; white-space: nowrap; font-weight:600;">For Your Better Future 
                </div>

                <!-- LAYER NR. 3 -->
                <div class="tp-caption tp-resizeme text-white text-right" 
                  id="rs-3-layer-3"

                  data-x="['right']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['30']"
                  data-fontsize="['16']"
                  data-lineheight="['28']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;s:500"
                  data-transform_in="y:100;scaleX:1;scaleY:1;opacity:0;"
                  data-transform_out="x:left(R);s:1000;e:Power3.easeIn;s:1000;e:Power3.easeIn;"
                  data-mask_in="x:0px;y:0px;s:inherit;e:inherit;"
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:0px; font-weight:400;">We provides always our best For students and always<br> try to achieve our student's trust and satisfaction.
                </div>

                <!-- LAYER NR. 4 -->
                <div class="tp-caption tp-resizeme" 
                  id="rs-3-layer-4"

                  data-x="['right']"
                  data-hoffset="['35']"
                  data-y="['middle']"
                  data-voffset="['95']"
                  data-width="none"
                  data-height="none"
                  data-whitespace="nowrap"
                  data-transform_idle="o:1;"
                  data-transform_in="y:[100%];z:0;rX:0deg;rY:0;rZ:0;sX:1;sY:1;skX:0;skY:0;opacity:0;s:2000;e:Power4.easeInOut;" 
                  data-transform_out="y:[100%];s:1000;e:Power2.easeInOut;s:1000;e:Power2.easeInOut;" 
                  data-mask_in="x:0px;y:[100%];s:inherit;e:inherit;" 
                  data-mask_out="x:inherit;y:inherit;s:inherit;e:inherit;"
                  data-start="1400" 
                  data-splitin="none" 
                  data-splitout="none" 
                  data-responsive_offset="on"
                  style="z-index: 5; white-space: nowrap; letter-spacing:1px;"><a class="btn btn-colored btn-lg btn-flat btn-theme-colored btn-theme-colored border-right-theme-color-2-6px pl-20 pr-20" href="student_reg.php">Apply Now</a> 
                </div>
              </li>

            </ul>
          </div>
          <!-- end .rev_slider -->
        </div>
        <!-- end .rev_slider_wrapper -->
        <script>
          $(document).ready(function(e) {
            $(".rev_slider").revolution({
              sliderType:"standard",
              sliderLayout: "auto",
              dottedOverlay: "none",
              delay: 5000,
              navigation: {
                  keyboardNavigation: "off",
                  keyboard_direction: "horizontal",
                  mouseScrollNavigation: "off",
                  onHoverStop: "off",
                  touch: {
                      touchenabled: "on",
                      swipe_threshold: 75,
                      swipe_min_touches: 1,
                      swipe_direction: "horizontal",
                      drag_block_vertical: false
                  },
                arrows: {
                  style:"zeus",
                  enable:true,
                  hide_onmobile:true,
                  hide_under:600,
                  hide_onleave:true,
                  hide_delay:200,
                  hide_delay_mobile:1200,
                  tmp:'<div class="tp-title-wrap">    <div class="tp-arr-imgholder"></div> </div>',
                  left: {
                    h_align:"left",
                    v_align:"center",
                    h_offset:30,
                    v_offset:0
                  },
                  right: {
                    h_align:"right",
                    v_align:"center",
                    h_offset:30,
                    v_offset:0
                  }
                },
                bullets: {
                  enable:true,
                  hide_onmobile:true,
                  hide_under:600,
                  style:"metis",
                  hide_onleave:true,
                  hide_delay:200,
                  hide_delay_mobile:1200,
                  direction:"horizontal",
                  h_align:"center",
                  v_align:"bottom",
                  h_offset:0,
                  v_offset:30,
                  space:5,
                  tmp:'<span class="tp-bullet-img-wrap">  <span class="tp-bullet-image"></span></span><span class="tp-bullet-title">{{title}}</span>'
                }
              },
              responsiveLevels: [1240, 1024, 778],
              visibilityLevels: [1240, 1024, 778],
              gridwidth: [1170, 1024, 778, 480],
              gridheight: [650, 768, 960, 720],
              lazyType: "none",
              parallax: {
                  origo: "slidercenter",
                  speed: 1000,
                  levels: [5, 10, 15, 20, 25, 30, 35, 40, 45, 46, 47, 48, 49, 50, 100, 55],
                  type: "scroll"
              },
              shadow: 0,
              spinner: "off",
              stopLoop: "on",
              stopAfterLoops: 0,
              stopAtSlide: -1,
              shuffle: "off",
              autoHeight: "off",
              fullScreenAutoWidth: "off",
              fullScreenAlignForce: "off",
              fullScreenOffsetContainer: "",
              fullScreenOffset: "0",
              hideThumbsOnMobile: "off",
              hideSliderAtLimit: 0,
              hideCaptionAtLimit: 0,
              hideAllCaptionAtLilmit: 0,
              debugMode: false,
              fallbacks: {
                  simplifyAll: "off",
                  nextSlideOnWindowFocus: "off",
                  disableFocusListener: false,
              }
            });
          });
        </script>
        <!-- Slider Revolution Ends -->

      </div>
    </section>

    <!-- Section:about-->
    <section>
      <div class="container pb-60">
        <div class="section-content">
          <div class="row">
            <div class="col-md-8">
              <div class="meet-doctors">
                <h2 class="text-uppercase mt-0 line-height-1">Welcome To <span class="text-theme-colored">Sharnav Institute</span></h2>
                <h6 class="text-uppercase letter-space-5 line-bottom title font-playfair text-uppercase">Education and Technology Pvt. Ltd.</h6>
                <p>Sharnay Institute of Education and Technology Pvt. Ltd. is among Bihar best computer training facilities. Along with classes like ADCA, DCA, and Tally, we offer training in all the most recent and complex subjects, such as GST, Hardware, and Digital Marketing. But we are the best. The Adv provides the most enjoyable computer and live classes Training Institute in Bihar, where students can hone their skills before using them in the workplace. During the week, we give students individualized and fast-track computer training in both online and traditional classroom settings. The most recent technologies allow students to complete computer training and certification in our lab. The only goal of our institute is to provide the best ADCA Course in Bihar.</p>
                <p>
                Sharnav Institute of Education and Technology Pvt. Ltd. have placed many students in all of its courses, and our instructional strategies will help the learners land the ideal job in their field of study!
                </p>
              </div>
             
            </div>
         <?php include 'enquiry.php'; ?>
          </div>
        </div>
      </div>
    </section>
    <!-- Section: About End -->
    
    <!-- Section: Our team -->
    <section class="our-team" aria-label="Our Team">
      <div class="bg-gradient"></div>
      <div class="bg-blob b1" aria-hidden="true"></div>
      <div class="bg-blob b2" aria-hidden="true"></div>
      <div class="bg-vignette" aria-hidden="true"></div>

      <div class="container">
        <div class="section-title">
          <h2>Our Team</h2>
          <p class="text-muted">Meet the people who make things happen</p>
        </div>

        <div class="role-row" role="tablist" aria-label="Filter team by role">
          <?php
          echo role_link('State Node', $filter);
          echo role_link('Zonal Manager', $filter);
          echo role_link('Dristic Co-Ordinator', $filter); // original label
          echo role_link('Block Co-Ordinator', $filter);
          ?>
        </div>

        <div class="team-grid" role="list">
          <?php if ($team_result && $team_result->num_rows > 0): ?>
            <?php while ($member = $team_result->fetch_assoc()): ?>
              <?php
                // safe defaults and escaping
                $username = htmlspecialchars((string)($member['owner_name'] ?? 'Unnamed'), ENT_QUOTES, 'UTF-8');
                $member_type = htmlspecialchars((string)($member['member_type'] ?? 'Member'), ENT_QUOTES, 'UTF-8');
                $district = htmlspecialchars((string)($member['district'] ?? '—'), ENT_QUOTES, 'UTF-8');

                // photo handling: use basename to avoid directory traversal and check file_exists
                $photo_file = (string)($member['photo'] ?? '');
                $safe_basename = $photo_file !== '' ? basename($photo_file) : '';
                $photo_path = $safe_basename !== '' ? $uploads_dir . $safe_basename : '';
                if ($safe_basename !== '' && file_exists($photo_path) && is_file($photo_path)) {
                    // encode filename part for URL
                    $photo_url = 'admin/uploads/' . rawurlencode($safe_basename);
                } else {
                    $photo_url = 'images/no-img-avatar.png';
                }
              ?>

              <article class="team-card" tabindex="0" role="listitem" aria-label="<?php echo $username . ' — ' . $member_type; ?>">
                <img class="avatar" src="<?php echo $photo_url; ?>" alt="<?php echo $username; ?>">
                <div class="name"><?php echo $username; ?></div>
                <div class="role"><?php echo $member_type; ?></div>
                <div class="district"><?php echo $district; ?></div>
              </article>
            <?php endwhile; ?>
          <?php else: ?>
            <div style="grid-column:1/-1; text-align:center; padding:28px 12px; color:#46525a;">
              No team members found for this role.
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
    <!-- end section: Our team -->
    
    <!-- Section: Enhanced Courses with Tabs -->
    <section class="courses-tab-section">
      <div class="container">
        <div class="section-header text-center">
          <h2>Our <span>Courses</span></h2>
          <p>Choose from a wide range of professional courses designed for your career growth</p>
        </div>
        
        <!-- Tab Navigation -->
        <div class="course-tabs-nav">
          <?php foreach($courses as $key => $category): ?>
            <button class="tab-btn <?php echo $active_tab == $key ? 'active' : ''; ?>" data-tab="<?php echo $key; ?>">
              <i class="fas <?php echo $category['icon']; ?>"></i>
              <span><?php echo $category['title']; ?></span>
              <span class="duration-badge-small">(<?php echo $category['duration_text']; ?>)</span>
            </button>
          <?php endforeach; ?>
        </div>
        
        <!-- Tab Content -->
        <div class="tabs-container">
          <?php foreach($courses as $key => $category): ?>
            <div class="tab-content <?php echo $active_tab == $key ? 'active' : ''; ?>" id="tab-<?php echo $key; ?>">
              <div class="category-header">
                <div class="category-icon">
                  <i class="fas <?php echo $category['icon']; ?>"></i>
                </div>
                <h3><?php echo $category['title']; ?></h3>
                <div class="duration-badge">
                  <i class="far fa-calendar-alt"></i> <?php echo $category['duration_text']; ?> Programs
                </div>
                <p><?php echo $category['description']; ?></p>
              </div>
              
              <div class="course-grid">
                <?php if(!empty($category['courses'])): ?>
                  <?php foreach($category['courses'] as $course): ?>
                    <div class="course-card" data-course-code="<?php echo $course['code']; ?>">
                      <div class="course-badge"><?php echo $course['code']; ?></div>
                      <h4 class="course-name"><?php echo $course['name']; ?></h4>
                      <p class="course-fullname"><?php echo $course['full_name']; ?></p>
                      <div class="course-meta">
                        <span class="duration">
                          <i class="far fa-calendar-alt"></i> <?php echo $course['duration']; ?>
                        </span>
                        <span class="fee">
                          <i class="fas fa-rupee-sign"></i> <?php echo $course['fee_range']; ?>
                        </span>
                      </div>
                      <button class="enquire-btn" onclick="showEnquiryForm('<?php echo addslashes($course['name']); ?>')">
                        <i class="fas fa-envelope"></i> Enquire Now
                      </button>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <p>No courses available in this category.</p>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <section class="divider parallax layer-overlay overlay-theme-colored-9" data-bg-img="images/s2.jpg" data-parallax-ratio="0.7">
      <h2 class="text-white" style="text-align: center;">100% JOB ORIENTED TRAINING</h2>
      <div class="container">
        <div class="row">
          <div class="col-xs-12 col-sm-6 col-md-3 mb-md-50">
            <div class="funfact text-center">
              <i class="pe-7s-smile mt-5 text-theme-color-2"></i>
              <h2 data-animation-duration="2000" data-value="5248" class="animate-number text-white mt-0 font-38 font-weight-500">0</h2>
              <h5 class="text-white text-uppercase mb-0">Happy Students</h5>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 mb-md-50">
            <div class="funfact text-center">
              <i class="pe-7s-note2 mt-5 text-theme-color-2"></i>
              <h2 data-animation-duration="2000" data-value="675" class="animate-number text-white mt-0 font-38 font-weight-500">0</h2>
              <h5 class="text-white text-uppercase mb-0">Our Courses</h5>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 mb-md-50">
            <div class="funfact text-center">
              <i class="pe-7s-users mt-5 text-theme-color-2"></i>
              <h2 data-animation-duration="2000" data-value="248" class="animate-number text-white mt-0 font-38 font-weight-500">0</h2>
              <h5 class="text-white text-uppercase mb-0">Our Teachers</h5>
            </div>
          </div>
          <div class="col-xs-12 col-sm-6 col-md-3 mb-md-0">
            <div class="funfact text-center">
              <i class="pe-7s-cup mt-5 text-theme-color-2"></i>
              <h2 data-animation-duration="2000" data-value="24" class="animate-number text-white mt-0 font-38 font-weight-500">0</h2>
              <h5 class="text-white text-uppercase mb-0">Awards Won</h5>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section: Why Choose Us -->
    <section id="event">
      <div class="container">
        <div class="section-content">
         <div class="row">
            <div class="col-md-5"> 
             <img src="images/photos/1.jpg" class="img-fullwidth" alt="">
            </div>
            <div class="col-md-7 pb-sm-20">
              <h3 class="title line-bottom mb-20 font-28 mt-0 line-height-1">Why <span class="text-theme-color-2 font-weight-400">Choose Sharnay Institute</span> ?</h3>
              <p class="mb-20">Sharnay Institute of Education and Technology Pvt. Ltd. is a trusted and reputed computer training institute in Bihar. We offer a wide range of online and offline courses like Data Analytics, Business Analytics, Data Science, ADCA, DCA, Tally ERP 9 with GST, Digital Marketing, and Advance Excel. After completing the course, you can land the best job in IT and non-IT companies.</p>
              <div class="col-sm-6 col-md-3 wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.3s">
                <div class="icon-box text-center pl-0 pr-0 mb-0">
                  <a href="#" class="icon bg-theme-colored icon-circled icon-border-effect effect-circle icon-md">
                    <i class="pe-7s-phone text-white"></i>
                  </a>
                  <h5 class="icon-box-title mt-15 mb-10 letter-space-4 text-uppercase"><strong>Practical Training </strong></h5>
                </div>
              </div>
              <div class="col-sm-6 col-md-3 wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.3s">
                <div class="icon-box text-center pl-0 pr-0 mb-0">
                  <a href="#" class="icon bg-theme-color-2 icon-circled icon-border-effect effect-circle icon-md">
                    <i class="pe-7s-pen text-white"></i>
                  </a>
                  <h5 class="icon-box-title mt-15 mb-10 letter-space-4 text-uppercase"><strong>100% Job-Oriented </strong></h5>
                </div>
              </div>
              <div class="col-sm-6 col-md-3 wow fadeInLeft" data-wow-duration="1s" data-wow-delay="0.3s">
                <div class="icon-box text-center pl-0 pr-0 mb-0">
                  <a href="#" class="icon bg-theme-colored icon-circled icon-border-effect effect-circle icon-md">
                    <i class="pe-7s-light text-white"></i>
                  </a>
                  <h5 class="icon-box-title mt-15 mb-0 letter-space-4 text-uppercase"><strong>Experienced Trainers</strong></h5>
                </div>
              </div>
             </div>
           </div>
         </div>
       </div>
    </section>
    
    <section class="divider parallax layer-overlay overlay-theme-colored-9" data-background-ratio="0.5" data-bg-img="images/s2.jpg">
      <div class="container pt-60 pb-60">
        <div class="row">
          <div class="col-md-8 col-md-offset-2">
            <h2 class="line-bottom-center text-gray-lightgray text-center mt-0 mb-30">Our Happy Students say</h2>
            <div class="owl-carousel-1col" data-dots="true">
              <div class="item">
                <div class="testimonial-wrapper text-center">
                  <div class="thumb"><img class="img-circle" alt="" src="images/testimonials/3.jpg"></div>
                  <div class="content pt-10">
                    <p class="font-15 text-white"><em>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque est quasi, quas ipsam, expedita placeat facilis odio illo ex accusantium eaque itaque officiis et sit. Vero quo, impedit neque.</em></p>
                    <i class="fa fa-quote-right font-36 mt-10 text-gray-lightgray"></i>
                    <h4 class="author text-theme-color-2 mb-0">Catherine Grace</h4>
                    <h6 class="title text-white mt-0 mb-15">Designer</h6>
                  </div>
                </div>
              </div>
              <div class="item">
                <div class="testimonial-wrapper text-center">
                  <div class="thumb"><img class="img-circle" alt="" src="images/testimonials/3.jpg"></div>
                  <div class="content pt-10">
                    <p class="font-15 text-white"><em>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque est quasi, quas ipsam, expedita placeat facilis odio illo ex accusantium eaque itaque officiis et sit. Vero quo, impedit neque.</em></p>
                    <i class="fa fa-quote-right font-36 mt-10 text-gray-lightgray"></i>
                    <h4 class="author text-theme-color-2 mb-0">Catherine Grace</h4>
                    <h6 class="title text-white mt-0 mb-15">Designer</h6>
                  </div>
                </div>
              </div>
              <div class="item">
                <div class="testimonial-wrapper text-center">
                  <div class="thumb"><img class="img-circle" alt="" src="images/testimonials/3.jpg"></div>
                  <div class="content pt-10">
                    <p class="font-15 text-white"><em>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Neque est quasi, quas ipsam, expedita placeat facilis odio illo ex accusantium eaque itaque officiis et sit. Vero quo, impedit neque.</em></p>
                    <i class="fa fa-quote-right font-36 mt-10 text-gray-lightgray"></i>
                    <h4 class="author text-theme-color-2 mb-0">Catherine Grace</h4>
                    <h6 class="title text-white mt-0 mb-15">Designer</h6>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
    
  <!-- end main-content -->
  </div>

  <!-- Footer -->
 <?php include 'footer.php'; ?>
  <a class="scrollToTop" href="#"><i class="fa fa-angle-up"></i></a>
</div>
<!-- end wrapper -->

<!-- Footer Scripts -->
<!-- JS | Custom script for all pages -->
<script src="js/custom.js"></script>

<!-- SLIDER REVOLUTION 5.0 EXTENSIONS  
      (Load Extensions only on Local File Systems ! 
       The following part can be removed on Server for On Demand Loading) -->
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.actions.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.carousel.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.kenburn.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.layeranimation.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.migration.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.navigation.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.parallax.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.slideanims.min.js"></script>
<script type="text/javascript" src="js/revolution-slider/js/extensions/revolution.extension.video.min.js"></script>

<script>
// Tab switching functionality with URL update
$(document).ready(function() {
    // Tab click handler
    $('.tab-btn').on('click', function() {
        var tabId = $(this).data('tab');
        
        // Update active state on buttons
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        
        // Show corresponding tab content
        $('.tab-content').removeClass('active');
        $('#tab-' + tabId).addClass('active');
        
        // Update URL without reloading
        var url = new URL(window.location.href);
        url.searchParams.set('course_tab', tabId);
        window.history.pushState({}, '', url);
    });
    
    // Handle browser back/forward buttons
    window.addEventListener('popstate', function() {
        var urlParams = new URLSearchParams(window.location.search);
        var tabId = urlParams.get('course_tab');
        if (tabId && $('#tab-' + tabId).length) {
            $('.tab-btn').removeClass('active');
            $('.tab-btn[data-tab="' + tabId + '"]').addClass('active');
            $('.tab-content').removeClass('active');
            $('#tab-' + tabId).addClass('active');
        }
    });
});

function showEnquiryForm(courseName) {
    // You can customize this function to show a modal or redirect to enquiry page
    alert('Enquiry for course: ' + courseName + '\nPlease fill the enquiry form to get more details.');
    // window.location.href = 'enquiry.php?course=' + encodeURIComponent(courseName);
}
</script>
</body>

</html>
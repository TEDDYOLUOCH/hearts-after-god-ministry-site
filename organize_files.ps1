# Create necessary directories
$directories = @(
    "frontend\assets\css",
    "frontend\assets\js", 
    "frontend\assets\images",
    "frontend\pages",
    "frontend\includes",
    "backend\api",
    "backend\config",
    "backend\includes"
)

foreach ($dir in $directories) {
    if (-not (Test-Path $dir)) {
        New-Item -ItemType Directory -Path $dir -Force | Out-Null
    }
}

# Move frontend pages
$frontendPages = @(
    "about.html",
    "bible-study.html",
    "blog-detail.html",
    "blog.php",
    "contact.php",
    "discipleship.html",
    "events.php",
    "favorites.html",
    "gallery.php",
    "index.html",
    "ministries.php",
    "missions.html",
    "outreach.html",
    "prayer.html",
    "programmes.html",
    "sermon-detail.html",
    "sermons.php",
    "team.php",
    "vision.html",
    "worship.html",
    "youth.html"
)

foreach ($page in $frontendPages) {
    if (Test-Path $page) {
        Move-Item -Path $page -Destination "frontend\pages\" -Force
    }
}

# Move includes
$includes = @(
    "header.html",
    "footer.html"
)

foreach ($include in $includes) {
    if (Test-Path $include) {
        Move-Item -Path $include -Destination "frontend\includes\" -Force
    }
}

# Move assets (you might need to adjust these paths based on your actual assets)
if (Test-Path "assets") {
    Get-ChildItem -Path "assets" -Recurse | Move-Item -Destination "frontend\assets" -Force
}

# Move API files
if (Test-Path "api") {
    Get-ChildItem -Path "api" -Recurse | Move-Item -Destination "backend\api" -Force
}

# Move config files
$configFiles = @(
    "config.php"
)

foreach ($config in $configFiles) {
    if (Test-Path $config) {
        Move-Item -Path $config -Destination "backend\config\" -Force
    }
}

Write-Host "Files have been organized!" -ForegroundColor Green

# Get all files
$files = Get-ChildItem -Path . -Recurse -File | Where-Object { $_.Extension -match '\.(php|js|css|html|json)$' }

# Group files by size first (faster than hashing everything)
$sizeGroups = $files | Group-Object Length | Where-Object { $_.Count -gt 1 }

# For files with same size, check hashes
$duplicates = @()
foreach ($group in $sizeGroups) {
    $hashGroups = $group.Group | Get-FileHash -Algorithm MD5 | Group-Object Hash | Where-Object { $_.Count -gt 1 }
    foreach ($hashGroup in $hashGroups) {
        $duplicates += $hashGroup.Group
    }
}

# Output results
if ($duplicates.Count -gt 0) {
    Write-Host "`nFound $($duplicates.Count) duplicate files:" -ForegroundColor Yellow
    $duplicates | Group-Object Hash | ForEach-Object {
        Write-Host "`nDuplicate group (Hash: $($_.Name))" -ForegroundColor Cyan
        $_.Group | Select-Object Path, @{Name="Size (KB)";Expression={"{0:N2}" -f ($_.Path | Get-Item).Length/1KB}} | Format-Table -AutoSize
    }
} else {
    Write-Host "No duplicate files found." -ForegroundColor Green
}

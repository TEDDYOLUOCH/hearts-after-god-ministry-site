# Function to get file hash
function Get-FileHashRecursive {
    param (
        [string]$Path
    )
    
    Get-ChildItem -Path $Path -Recurse -File | ForEach-Object {
        $hash = (Get-FileHash -Algorithm MD5 -Path $_.FullName).Hash
        [PSCustomObject]@{
            Path = $_.FullName
            Name = $_.Name
            Length = $_.Length
            Hash = $hash
            LastWriteTime = $_.LastWriteTime
        }
    }
}

# Get all files and their hashes
$allFiles = Get-FileHashRecursive -Path .

# Find duplicates by hash
$duplicates = $allFiles | Group-Object Hash | Where-Object { $_.Count -gt 1 } | Sort-Object Count -Descending

# Output results
$duplicates | ForEach-Object {
    Write-Host "`nDuplicate files (Hash: $($_.Name))" -ForegroundColor Yellow
    Write-Host "========================================"
    $_.Group | Select-Object Path, Name, @{Name="Size (KB)";Expression={"{0:N2}" -f ($_.Length/1KB)}}, LastWriteTime | Format-Table -AutoSize
}

Write-Host "`nScan complete. Found $($duplicates.Count) sets of duplicate files." -ForegroundColor Green

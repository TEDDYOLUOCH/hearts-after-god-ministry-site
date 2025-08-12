# Function to find and remove duplicate files
function Remove-DuplicateFiles {
    param (
        [string]$Directory = "."
    )
    
    Write-Host "Scanning for duplicate files in: $Directory" -ForegroundColor Cyan
    
    # Get all files and group them by size first (faster than hashing everything)
    $filesBySize = Get-ChildItem -Path $Directory -Recurse -File | 
                  Where-Object { $_.Extension -match '\.(php|js|css|html|json|sql)$' } |
                  Group-Object Length | 
                  Where-Object { $_.Count -gt 1 }
    
    $totalDuplicates = 0
    $totalSpaceSaved = 0
    
    foreach ($sizeGroup in $filesBySize) {
        # For files with same size, check hashes
        $filesByHash = $sizeGroup.Group | 
                      Get-FileHash -Algorithm MD5 | 
                      Group-Object Hash | 
                      Where-Object { $_.Count -gt 1 }
        
        foreach ($hashGroup in $filesByHash) {
            $duplicates = $hashGroup.Group | Sort-Object Path
            $original = $duplicates[0]
            $duplicatesToRemove = $duplicates | Select-Object -Skip 1
            
            Write-Host "`nFound $($duplicates.Count) duplicates of: $($original.Path)" -ForegroundColor Yellow
            
            foreach ($duplicate in $duplicatesToRemove) {
                $duplicateSize = (Get-Item $duplicate.Path).Length
                Write-Host "  - Removing duplicate: $($duplicate.Path) (Size: $($duplicateSize/1KB) KB)" -ForegroundColor Red
                Remove-Item -Path $duplicate.Path -Force
                $totalDuplicates++
                $totalSpaceSaved += $duplicateSize
            }
        }
    }
    
    Write-Host "`nCleanup complete!" -ForegroundColor Green
    Write-Host "Total duplicates removed: $totalDuplicates" -ForegroundColor Green
    Write-Host "Total space saved: $([math]::Round($totalSpaceSaved/1MB, 2)) MB" -ForegroundColor Green
}

# Run the function
Remove-DuplicateFiles -Directory "."

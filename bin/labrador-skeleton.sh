#!/bin/bash

# A shell script that will generate appropriate directories and files to get
# Labrador up and running from a fresh Composer install.

# This script is safe to run on an already existing installation and setup. If
# there are any conflicts creating or copying over the appropriate files we
# will skip that file and your existing code will not be changed.

scriptPath=$(pwd)
labPath="$scriptPath/vendor/cspray/labrador"
projectName=$1
includeSrc=0

if [ ! -d "$labPath" ]
then
    echo "Labrador could not be found at ./vendor/cspray/labrador ... ABORTING!"
    exit 255
fi

echo "Creating new Labrador project named $projectName"
echo

dirs=("src" "public" "public/css" "public/img" "public/js" "config")
for dir in "${dirs[@]}"
    do
        fullItem="$scriptPath/$dir"
        if [ -d "$fullItem" ]
        then
            echo "$fullItem already exists ... SKIPPING!"
        else
            if [ -w "$fullItem" ]
            then
                echo "WARNING! $item is not writable ... SKIPPING!"
            else
                printf "Creating $fullItem ... "
                mkdir -p "$fullItem"; echo "SUCCESS!" || echo "FAILED!"
            fi
        fi
    done

echo
echo "Copying over configuration and setup files..."
echo

files=("init.php" "public/index.php" "public/css/normalize.css" "public/css/prism.css" "public/js/zepto.min.js" "public/js/prism.js")
for file in "${files[@]}"
    do
        appItem="$scriptPath/$file"
        labItem="$labPath/$file"
        if [ -f "$appItem" ]
        then
            echo "$appItem already exists ... SKIPPING!"
        else
            if [ -f "$labItem" ]
            then
                printf "Creating $appItem ... "
                cp "$labItem" "$appItem"; echo "SUCCESS!" || echo "FAILED!"
            else
                echo "$labItem does not exist ... SKIPPING!"
            fi
        fi
    done

echo
echo "Finished creating your project!"

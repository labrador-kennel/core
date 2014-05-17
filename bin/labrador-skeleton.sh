#!/bin/sh

scriptPath=$(pwd)
labPath="$scriptPath/vendor/cspray/labrador"
projectName=$1

# Check to make sure that there is at least some project name given
if [ -z "$projectName" ]
then
    echo "You must provide a valid project name to get started"
    exit 255
fi

if [ ! -d "$labPath" ]
then
    echo "Labrador could not be found at $labPath...ABORTING!"
    exit 255
fi

echo "Creating new Labrador project named $projectName"
echo

dirs=("$scriptPath/src/$projectName" "$scriptPath/public" "$scriptPath/config")

for item in "${dirs[@]}"
    do
        if [ -d "$item" ]
        then
            echo "$item already exists ... skipping"
        else
            if [ -w "$item" ]
            then
                echo "WARNING! $item is not writable ... skipping"
            else
                echo "Creating $item ... "
                mkdir -p "$item"
            fi
        fi
    done

cp "$labPath/init.php" "$scriptPath/init.php"
cp "$labPath/config/master_config.php" "$scriptPath/config/master_config.php"
cp "$labPath/config/bootstraps.php" "$scriptPath/config/bootstraps.php"
cp "$labPath/config/services.php" "$scriptPath/config/services.php"
cp "$labPath/config/routes.php" "$scriptPath/config/routes.php"
cp "$labPath/public/index.php" "$scriptPath/public/index.php"




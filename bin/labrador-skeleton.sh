#!/bin/bash

scriptPath=$(pwd)
labPath="$scriptPath/vendor/cspray/labrador"
projectName=$1
includeSrc=0

if [ ! -d "$labPath" ]
then
    echo "Labrador could not be found at ./vendor/cspray/labrador ... ABORTING!"
    exit 255
fi

if [ -z "$projectName" ]
then
    echo "No project name was given for creation of /src directory ... SKIPPING!"
    dirs=("$scriptPath/public" "$scriptPath/config")
else
    dirs=("$scriptPath/src/$projectName" "$scriptPath/public" "$scriptPath/config")
fi

echo "Creating new Labrador project named $projectName"
echo

for item in "${dirs[@]}"
    do
        if [ -d "$item" ]
        then
            echo "$item already exists ... SKIPPING!"
        else
            if [ -w "$item" ]
            then
                echo "WARNING! $item is not writable ... SKIPPING!"
            else
                echo "Creating $item ... \c"
                mkdir -p "$item" || echo "FAILED!"
            fi
        fi
    done

echo "Copying over configuration and setup files..."

if [ -f "$scriptPath/init.php" ]
then
    echo "$scriptPath/init.php already exists ... skipping"
else
    cp "$labPath/init.php" "$scriptPath/init.php"
fi

if [ -f "$scriptPath/config/master_config.php" ]
then
    echo "$scriptPath/config/master_config.php already exists ... skipping"
else
    cp "$labPath/config/master_config.php" "$scriptPath/config/master_config.php"
fi

if [ -f "$scriptPath/config/bootstraps.php" ]
then
    echo "$scriptPath/config/bootstraps.php already exists ... skipping"
else
    cp "$labPath/config/bootstraps.php" "$scriptPath/config/bootstraps.php"
fi

if [ -f "$scriptPath/config/services.php" ]
then
    echo "$scriptPath/config/services.php already exists ... skipping"
else
    cp "$labPath/config/services.php" "$scriptPath/config/services.php"
fi

if [ -f "$scriptPath/config/routes.php" ]
then
    echo "$scriptPath/config/routes.php already exists ... skipping"
else
    cp "$labPath/config/routes.php" "$scriptPath/config/routes.php"
fi

if [ -f "$scriptPath/public/index.php" ]
then
    echo "$scriptPath/public/inxed.php already exists ... skipping"
else
    cp "$labPath/public/index.php" "$scriptPath/public/index.php"
fi

echo
echo "Finished creating your project!"

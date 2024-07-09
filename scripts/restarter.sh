#!/bin/bash

apacheStatus=$(service apache2 status)

if [[ $apacheStatus == *"active (running)"* ]]; then
  echo "apache2 is running"
  sudo systemctl restart apache2
else
  echo "apache2 is not running"
  sudo systemctl start apache2
fi

#!/bin/bash
rsync -av --no-g --no-t --no-p --no-o --exclude="*~" --exclude=".git*" --exclude=syncup.sh ./ /var/www/testbed/testbed/
 

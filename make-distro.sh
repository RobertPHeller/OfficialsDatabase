#!/bin/bash
mkdir officials-1.0
find . | grep -v '/\.git'| grep -v '~$' |  grep -v 'officials-1\.0' |\
    cpio -p --make-directories --link officials-1.0
tar czvf officials-1.0.tar.gz officials-1.0 
rm -f officials-1.0.zip
zip -r officials-1.0.zip officials-1.0
rm -rf officials-1.0

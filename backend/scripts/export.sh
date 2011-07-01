#!/bin/bash

EXPORTDIR=/var/www/tadah.mine.nu/export/

# Drop the frontend directory
'rm' -rf "${EXPORTDIR}/frontend/"

# Export the svn source
svn export http://rkuipers.mine.nu/svn/dpcstats ${EXPORTDIR}/dpcstats/

# Create the tar file
cd ${EXPORTDIR} && tar -cf stats.tar dpcstats/

# Zip the file
bzip2 -f ${EXPORTDIR}/stats.tar

# Drop the backend directory
'rm' -rf "${EXPORTDIR}/dpcstats/"

# Dump the database scheme
mysqldump -h 192.168.0.7 stats -d > ${EXPORTDIR}/database.sql

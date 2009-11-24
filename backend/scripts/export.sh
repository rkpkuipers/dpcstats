#!/bin/bash

EXPORTDIR=/var/www/tadah.mine.nu/export/

# Drop the frontend directory
'rm' -rf "${EXPORTDIR}/frontend/"

# Export the svn source
svn export http://rkuipers.mine.nu/svn/frontend ${EXPORTDIR}/frontend

# Create the tar file
cd ${EXPORTDIR} && tar -cf frontend.tar frontend/

# Zip the file
bzip2 -f ${EXPORTDIR}/frontend.tar

# Drop the backend directory
'rm' -rf "${EXPORTDIR}/backend/"

# Export the svn source
svn export http://rkuipers.mine.nu/svn/backend ${EXPORTDIR}/backend

# Create the tar file
cd ${EXPORTDIR} && tar -cf backend.tar backend/

# Zip the file
bzip2 -f ${EXPORTDIR}/backend.tar

# Dump the database scheme
mysqldump -h 192.168.0.7 stats -d > ${EXPORTDIR}/database.sql

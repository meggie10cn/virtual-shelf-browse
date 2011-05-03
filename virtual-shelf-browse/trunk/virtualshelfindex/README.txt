TERMS OF USE
    
    MIT/X11 License
    See included LICENSE.txt or http://www.opensource.org/licenses/mit-license.php

VIRTUAL SHELF INDEX AND WEB SERVICE

    The virtualshelfindex directory contains all the back end data processing scripts as well as
    the web service application that provides access to the mySQL shelf index.
    
DIRECTORIES AND FILES

    virtualshelfindex/ -- web service and data processing directory
        README.txt
        data/ -- delimited daily output from the ILS
            ils_output_pipe_delim.txt -- sample ILS output file -- replace with your own
        documentation/
            html/
                documentation.html --> web documentation for making web service requests
        handler/
            router.php --> directs requests to web service
            resources/
                handler.php --> main classes and database functions
                parameter_check_functions.php --> checks user supplied parameters
                call_number_padding_functions.php --> pads LC call numbers for sorting
                sudoc_padding_functions.php --> pads SUDOC call numbers for sorting
            start/
                start.php --> contains classes for handling start service requests
            slice/
                slice.php --> contains classes for handling slice service requests
        logs/
            build_process_log.txt -- > log file for data processesing and DB ingest script
        scripts/
            vs_index_ingest_calling_app.py --> main data processing and database ingest script
            vs_index_ingest_functions.py --> functions for ingest script

GENERAL PROJECT INFO

    http://www.lib.ncsu.edu/dli/projects/virtualshelfindex/

WEB SERVICE DOCUMENTATION

   http://YOURSERVER/virtualshelfindex-os/virtualshelfindex/documentation/html/documentation.html

BASE REQUEST

    http://YOURSERVER/virtualshelfindex-os/virtualshelfindex/handler/router.php
    
START REQUESTS

    http://YOURSERVER/virtualshelfindex-os/virtualshelfindex/handler/router.php?
    service=start&catKey=1394451
    
    http://YOURSERVER/virtualshelfindex-os/virtualshelfindex/handler/router.php?
    service=start&callNum=BF353%20.W356%202000

SLICE REQUEST

    http://YOURSERVER/virtualshelfindex-os/virtualshelfindex/handler/router.php?
    service=slice&batchId=500
    
GETTING STARTED

    - Create a MySQL database and modify handler.php with the correct database name and password
    - Makes sure the server has write/update/delete permissions on the database
    - You do not need to create a table in the database. The ingest script creates a table with
      the necessary structure.
    - Place the main virtualshelfindex-os directory with all subdirectories in a web accessible
      directory
    - Modify the scripts where necessary for your installation. Places in the script you are most
      likely to need to modify are indicated by ("MODIFY").
    - Generate a daily (or as necessary) output from your ILS in a pipe delimited format as 
      specified in vs_index_ingest_calling_app.py. By default the expected directory and name
      for this pipe delimited output is 
      /virtualshelfindex-os/virtualshelfindex/data/ils_output_pipe_delimited.txt
    - Multiple classification schemes (LC and SUDOC supported by default) may be present
      in the index. However, they should not be interfiled. We chose to list all LC first then
      all SUDOC.
    - Set up a cron job to run vs_index_ingest_calling_app.py daily (or as necessary) to recreate
      the database table of the index with the latest catalog data.
    - The first time you run vs_index_ingest_calling_app.py you'll need to run it with the
      --force flag
    
PYTHON & PHP VERSIONS

    Python code tested for use with Python 2.5.2
    PHP code tested for use with PHP 5.2.6
    (code is unlikely to work with PHP 4 or lower)
# Author: Cory Lown, cory_lown@ncsu.edu
# 
# Terms of Use: MIT License/X11 License
# 
# Copyright (C) 2010  NCSU Libraries, Raleigh, NC, http://www.lib.ncsu.edu/
# 
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
# 
# The above copyright notice and this permission notice shall be included in
# all copies or substantial portions of the Software.
# 
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
# THE SOFTWARE.

# This script is the data ingest script for the Virtual Shelf Index.
# In order to function the script requires its companion functions file
# to exist in the same directory: vs_index_ingest_functions.py.
# It also expects to find an ILS output that contains a pipe delimited
# file of all library records in shelf order that are wanted in the Index.
# The output from Sirsi must contain the following fields in the following
# order: catalog key, class type, padded call number, call number, location.
# The script creates a table to store the procesesed data. It generates the
# following output fields in the following order: item_id, group_id,
# catalog_key, call_number, call_number_search, call_number_padded,
# classification. Once the newly created table is procesessed the script
# renames the current table to OLD_sorted_call_no_index, renames the newly
# created table from NEW_sorted_call_no_index to sorted_call_no_index and then
# drops the old table. This is to prevent any downtime of the index.
#
# The first time you run this script you will need to do so manually from the 
# command line with the --force flag. Subsequently, the script may be run by cron 
# without the --force flag. This flag overrides a table size check that prevents 
# the old table from being overwritten if the new data looks too large or too 
# small.

import vs_index_ingest_functions as vsi_func
import os
import re
import pprint
import string
import MySQLdb
from optparse import OptionParser
import warnings

# define allowable command line arguments/flags
use = "Usage: %prog argument1"
parser = OptionParser(usage = use)
parser.add_option("-f", "--force", dest="force", action="store_true", default=False, help="Force script to update table.")
options, args = parser.parse_args()

# suppress specific expected mySQL warnings
warnings.filterwarnings("ignore", "Unknown table 'OLD_sorted_call_no_index'")
warnings.filterwarnings("ignore", "Unknown table 'NEW_sorted_call_no_index'")
warnings.filterwarnings("ignore", "Table 'sorted_call_no_index' already exists")

# defines the location of the output from sirsi to be processed
sirsi_extract_location = '../data/ils_output_pipe_delim.txt'

# log file location that caputures build process counts
log_file = '../logs/build_process_log.txt'


ingest_summary = {'Total Lines in Sirsi Output':0, 'Total Lines Included in Pre-Proc':0, 'Total Batches Ingested':0, 'Total Records Ingested':0}
count_lines_from_sirsi = 0
count_lines_included_pre_proc = 0

complete_list = []

# define line check regular expressions
check_for_unsorted_lines_pattern = re.compile ('\|.+\|.+\|.+\|.+\|.+\|.+\|$')
xx_callnumbers = re.compile('^\s*?ACQ\s*?SER\s*?XX\s*?\(.*?\)')

# define database login values
db_login_dict = {'host':'HOST', # MODIFY
'username': 'USERNAME', # MODIFY
'password': 'PW',  # MODIFY
'name': 'NAME'} # MODIFY

# define database table names
db_current_index_table = 'sorted_call_no_index'
db_new_index_table = 'NEW_sorted_call_no_index'
db_old_index_table = 'OLD_sorted_call_no_index'

# define table column names
db_columns_dict = {'item_id':{'name':'item_id','data_type':'INT'},
'group_id':{'name':'group_id','data_type':'INT'},
'catalog_key':{'name':'catalog_key','data_type':'INT'},
'call_number':{'name':'call_number','data_type':'VARCHAR(250)'},
'call_number_search':{'name':'call_number_search','data_type':'VARCHAR(250)'},
'call_number_padded':{'name':'call_number_padded','data_type':'VARCHAR(250)'},
'classification':{'name':'classification','data_type':'VARCHAR(25)'}}

# connect to db
db_conn = vsi_func.db_index_connect(db_login_dict, log_file, ingest_summary)
cursor = db_conn['cursor']
conn = db_conn['conn']

# cleanup orphaned tables in db if necessary
vsi_func.db_index_drop_table(cursor,db_old_index_table)
vsi_func.db_index_drop_table(cursor,db_new_index_table)

# attempt to open the Sirsi extract
try:
    file = open(sirsi_extract_location,'r')
except IOError, e:
    try:
        (code, message) = e
    except:
        code = 0
        message = e
    error_message = "Error(" + str(code) + "): " + message + sirsi_extract_location
    vsi_func.fatal_errors(error_message, log_file, ingest_summary)

# read in lines from the sirsi output and check to make sure
# all ingested lines from the output have the expected number
# of fields. Lines with extra fields are skipped.
for line in file:
    count_lines_from_sirsi += 1
    if not check_for_unsorted_lines_pattern.search(line):
        line_list = vsi_func.pre_proc_sirsi_output(line)
        complete_list.append(line_list)
        count_lines_included_pre_proc += 1
        
# create new table for new index and add placeholder for current table if it does not exist
try:
    vsi_func.db_index_create_table(cursor,db_new_index_table,db_columns_dict)
except:
    error_message = 'Could not create NEW table.'
    vsi_func.fatal_errors(error_message, log_file, ingest_summary)

# creates a placeholder current table if the current table does not already exist
vsi_func.db_index_create_table(cursor,db_current_index_table,db_columns_dict)
    
# begin proc for insert
primary_key = 0
group_count = -1
previous_item_key = 0

# adds an item_id and group_id to each record, applies functions to fields
# and attempts to insert the processed lines into the new database table.
for line in complete_list:
    if not xx_callnumbers.match(line[2]):
        if line[0] != previous_item_key:
            group_count = group_count + 1
        line_for_insert = vsi_func.proc_for_insert(group_count, primary_key, line)
        try:
            vsi_func.db_index_insert_values(cursor, db_new_index_table, line_for_insert)
        except:
            ingest_summary = vsi_func.ingest_summary(ingest_summary,count_lines_from_sirsi,count_lines_included_pre_proc,group_count,primary_key)
            error_message = 'Could not insert data into NEW table.'
            vsi_func.fatal_errors(error_message, log_file, ingest_summary)
        primary_key = primary_key + 1
        previous_item_key = line[0]


# drops old table if new table has number of rows within allowable limits or
# if the force argument was applied from the command line
if vsi_func.verify_new_table_against_current_table(cursor, db_new_index_table, db_current_index_table) or options.force:
    # rename current table to old table
    # rename new table to current table
    # drop old table
    # this procedure prevents downtime of index.
    vsi_func.db_index_rename_table(cursor,db_current_index_table,db_old_index_table)
    vsi_func.db_index_rename_table(cursor,db_new_index_table,db_current_index_table)
    vsi_func.db_index_drop_table(cursor,db_old_index_table)
else:
    ingest_summary = vsi_func.ingest_summary(ingest_summary,count_lines_from_sirsi,count_lines_included_pre_proc,group_count,primary_key)
    error_message = 'Too many or too few rows in new table.\nTo force build process run script with -f flag.'
    vsi_func.fatal_errors(error_message, log_file, ingest_summary)


# commit changes to the database
vsi_func.db_index_commit_changes(conn)

# log ingest process
ingest_summary = vsi_func.ingest_summary(ingest_summary,count_lines_from_sirsi,count_lines_included_pre_proc,group_count,primary_key)
vsi_func.output_to_log(log_file, ingest_summary)
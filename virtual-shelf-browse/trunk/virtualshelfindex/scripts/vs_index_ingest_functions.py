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

# This is the companion functions file for vs_index_ingest_function.py.
# It is called as vsi_func in the calling application. The functions file
# should reside in the same directory as the calling application.

import MySQLdb
import string
import sys
import time
import datetime
import os

fatal_error_message = None

# executes a query
def db_index_execute_query(cursor,query):
    cursor.execute(query)
    return

# creates a summary of the ingest process    
def ingest_summary(ingest_summary,count_lines_from_sirsi,count_lines_included_pre_proc,group_count,primary_key):
    ingest_summary['Total Lines in Sirsi Output'] = count_lines_from_sirsi
    ingest_summary['Total Lines Included in Pre-Proc'] = count_lines_included_pre_proc
    ingest_summary['Total Batches Ingested'] = group_count + 1
    ingest_summary['Total Records Ingested'] = primary_key
    return ingest_summary

# generates error messages, logs errors, and exits gracefully
def fatal_errors(error_message, log_file, ingest_summary):
    global fatal_error_message
    fatal_error_message = error_message
    output_to_log(log_file, ingest_summary)
    email_admin('VSI Ingest Script FATAL ERROR', error_message)
    sys.exit()

# generates log   
def output_to_log(log_file, ingest_summary):
    write_file = open(log_file,'a')
    write_file.write(time.ctime() + "\n")
    if fatal_error_message:
        write_file.write('FATAL ERROR: ' + fatal_error_message + '\n')
    write_file.write('Total Lines in Sirsi Output: ' + str(ingest_summary['Total Lines in Sirsi Output']) + '\n')
    write_file.write('Total Lines Included in Pre-Proc: ' + str(ingest_summary['Total Lines Included in Pre-Proc']) + '\n')
    write_file.write('Total Batches Ingested: ' + str(ingest_summary['Total Batches Ingested']) + '\n')
    write_file.write('Total Records Ingested: ' + str(ingest_summary['Total Records Ingested']) + '\n')
    write_file.write('\n********************************************************************\n')
    write_file.close()


# function returns false if new table is smaller or larger than current table by more than 5%
# TODO this will not be called if the -F (--FORCE) flag is used when running ths script
def verify_new_table_against_current_table(cursor, new_table_name, current_table_name):
    num_rows_new_table = db_get_row_count(cursor, new_table_name)
    num_rows_current_table = db_get_row_count(cursor, current_table_name)
    margin = num_rows_current_table * .05
    
    if num_rows_new_table < (num_rows_current_table + margin) and num_rows_new_table > (num_rows_current_table - margin):
        return 1
    else:
        return 0
        
def email_admin(email_subject, email_message_body):        
    MAIL = "/usr/sbin/sendmail"
    # get the email message from a file
    mssg = 'To: name@university.edu\nFROM: name@university.edu\nSUBJECT: ' + email_subject + '\n\n' + email_message_body #MODIFY WITH ADMIN EMAIL FOR SCRIPT ERROR ALERTS
    # open a pipe to the mail program and
    # write the data to the pipe
    p = os.popen("%s -t" % MAIL, 'w')
    p.write(mssg)
    p.close()
    
# preprocesses sirsi output lines, removes newline characters and splits each line at each pipe
# returns a list with each element of the line
def pre_proc_sirsi_output(line):
    line = line.rstrip('\n')
    line_list = []
    line_list  = line.split('|')
    return line_list

# processes each element of each line for input into the database
# creates a version of the call number for search without spaces
# cleans up the padded call number
def proc_for_insert(group_count, primary_key, line):
    line_for_insert = {}
    line_for_insert['item_id'] = str(primary_key)
    line_for_insert['group_id'] = str(group_count)
    line_for_insert['catalog_key'] = line[0]
    line_for_insert['call_number'] = line[3].strip().replace("'", "\\'")
    line_for_insert['call_number_search'] = string.join(line[3].split(), "").upper().replace("'", "\\'")
    line_for_insert['call_number_padded'] = line[2].replace("'", "\\'")
    line_for_insert['classification'] = line[1]
    return line_for_insert

# attempts to establish a database connection
def db_index_connect(db_login_dict, log_file, ingest_summary):
    db_conn = {}
    try:
        conn = MySQLdb.connect (host = db_login_dict['host'], user = db_login_dict['username'], passwd = db_login_dict['password'], db = db_login_dict['name'])
        db_conn['cursor'] = conn.cursor()
        db_conn['conn'] = conn
    except MySQLdb.Error, e:
        fatal_errors(e.args[1], log_file, ingest_summary)
    return db_conn

# generic function to creates a new table
def db_index_create_table(cursor,db_new_index_table,db_columns_dict):
    #TO DO ensure table is dropped if it exists
    #db_index_drop_table(cursor,db_new_index_table)
    sql_create_table = '''CREATE TABLE IF NOT EXISTS %s (%s %s PRIMARY KEY, %s %s, %s %s, %s %s, %s %s, %s %s, %s %s, INDEX (%s), INDEX (%s), INDEX(%s), INDEX(%s));'''%(db_new_index_table,db_columns_dict['item_id']['name'],db_columns_dict['item_id']['data_type'],db_columns_dict['group_id']['name'],db_columns_dict['group_id']['data_type'],db_columns_dict['catalog_key']['name'],db_columns_dict['catalog_key']['data_type'],db_columns_dict['call_number']['name'],db_columns_dict['call_number']['data_type'],db_columns_dict['call_number_search']['name'],db_columns_dict['call_number_search']['data_type'],db_columns_dict['call_number_padded']['name'],db_columns_dict['call_number_padded']['data_type'],db_columns_dict['classification']['name'],db_columns_dict['classification']['data_type'],db_columns_dict['group_id']['name'],db_columns_dict['catalog_key']['name'],db_columns_dict['call_number_search']['name'],db_columns_dict['call_number_padded']['name'])
    db_index_execute_query(cursor,sql_create_table)
    return
    
# generic function to insert values into a table
def db_index_insert_values(cursor, db_new_index_table, line_for_insert):
    sql_insert_values = '''INSERT INTO %s VALUES ('%s','%s','%s','%s','%s','%s','%s');'''%(db_new_index_table,line_for_insert['item_id'],line_for_insert['group_id'],line_for_insert['catalog_key'],line_for_insert['call_number'],line_for_insert['call_number_search'],line_for_insert['call_number_padded'],line_for_insert['classification'])
    db_index_execute_query(cursor,sql_insert_values)
    return

# generic function to rename a table
def db_index_rename_table(cursor,old_table_name,new_table_name):
    sql_rename = '''RENAME TABLE %s TO %s'''%(old_table_name,new_table_name)
    db_index_execute_query(cursor,sql_rename)
    return

# generic function to drop a table    
def db_index_drop_table(cursor,table_to_drop):
    sql_drop_table = '''DROP TABLE IF EXISTS %s'''%(table_to_drop)
    db_index_execute_query(cursor,sql_drop_table)
    return
    
# generic function to get number of rows in a table
def db_get_row_count(cursor, table_name):
    sql_get_row_count = '''SELECT COUNT(*) FROM %s'''%(table_name)
    db_index_execute_query(cursor,sql_get_row_count)
    rows = cursor.fetchall()
    for row in rows:
        rows_in_db = row[0]
    return rows_in_db
    
    
# commits changes to the db and closes the db connection
def db_index_commit_changes(conn):
   conn.commit ()
   conn.close ()
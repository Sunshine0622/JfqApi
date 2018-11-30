#!/usr/bin/python
# -*- coding: UTF-8 -*-
import MySQLdb
conn = MySQLdb.connect(host = '172.17.1.106',user = 'root',passwd = 'ttttottttomysql',db = 'aso_db',port = 3306,charset = 'utf8')
cursor  = conn.cursor()
conn1 = MySQLdb.connect(host = '172.17.1.107',user = 'root',passwd = 'ttttottttomysql',db = 'aso_db',port = 3306,charset = 'utf8')
cursor1  = conn1.cursor()

def del_file(path):
	import os
	ls = os.listdir(path)
	for i in ls:
		c_path = os.path.join(path, i)
		if  os.path.isdir(c_path):
			  del_file(c_path)
		else:
			  os.remove(c_path)

def GetFileList(FindPath, FlagStr = []):  
    ''''' 
    #获取目录中指定的文件名 
    #>>>FlagStr=['F','EMS','txt'] #要求文件名称中包含这些字符 
    #>>>FileList=GetFileList(FindPath,FlagStr) # 
    '''  
    import os  
    FileList  = []  
    FileNames = os.listdir(FindPath)  
    if (len(FileNames) > 0):  
       for fn in FileNames:  
           if (len(FlagStr) > 0):  
               #返回指定类型的文件名  
               if (IsSubString(FlagStr, fn)):  
                   fullfilename = os.path.join(FindPath, fn)  
                   FileList.append(fullfilename)  
           else:  
               #默认直接返回所有文件名  
               fullfilename = os.path.join(FindPath, fn)  
               FileList.append(fullfilename)  
  
    #对文件名排序  
    if (len(FileList) > 0):  
        FileList.sort()  
  
    return FileList

def importMysql(filePath, appid, Y, sheet="Sheet1"):
	''''' 
	#导入数据库
	#>>> Y 是表格列
	#>>>appid 苹果ID 
	'''  
	import xlrd
	import time
	bk       = xlrd.open_workbook(filePath)
	shxrange = range(bk.nsheets)
	try:
		sh = bk.sheet_by_name(bk.sheet_names()[0])
	except:
		print "no sheet in %s named " + sheet % fname
	#获取行数
	nrows = sh.nrows
	#获取列数
	ncols = sh.ncols
	print filePath
	print "nrows %d, ncols %d" % (nrows, ncols)
	#获取第一行第一列数据 

	row_list = []
	# 这里写1或者0, 表示idfa的开行位置
	for i in range(1,nrows):
		row_data = sh.cell_value(i, Y)
		row_list.append(row_data)
	# print(row_list)
	# 导入数据库

	try:
		dataList = []
		for x in xrange(len(row_list)):
			dataList.append((207, appid, row_list[x],1, int(time.time()), 2))
		sql = "INSERT INTO aso_submit(cpid,appid, idfa,adid,timestamp, type)VALUES (%s,%s, %s, %s, %s, %s)"
		ret = cursor.executemany(sql,dataList)
		print("success:" + str(ret))
		sql4    = 'UPDATE aso_file SET total = {total} WHERE id ={id}' \
		.format(total=str(ret),id=Id)
		try:
				# 执行SQL语句
				cursor1.execute(sql4)
				# 提交到数据库执行
				conn1.commit()
		except:
				# 发生错误时回滚
				conn1.rollback()
	except MySQLdb.Error,e:
		print "Mysql Error %d: %s" % (e.args[0], e.args[1])

# SQL 查询语句
sql = "SELECT * FROM aso_file \
       WHERE status = '%d' limit 1" % (0)
import time

# 执行SQL语句
cursor1.execute(sql)
# 获取所有记录列表
results = cursor1.fetchall()
#if not results.strip():
if len(results):
	for row in results: 
		Id      = row[0]
		fileDir = row[5]
		# 苹果ID
		appid   = row[1]
		# 表格第几列
		Y       = 0
		sheet   = "Sheet1"
		listDir = GetFileList(fileDir)
		t1       = time.time()
		sql2    = 'UPDATE aso_file SET start_time = {t1} WHERE id ={id}' \
		.format(t1=t1,id=Id)
		try:
				# 执行SQL语句
				cursor1.execute(sql2)
				# 提交到数据库执行
				conn1.commit()
		except:
				# 发生错误时回滚
				conn1.rollback()
    	for x in xrange(len(listDir)):
	    	importMysql(listDir[x], appid, Y)

		t2       = time.time()
	    # SQL 更新语句
		sql1 = 'UPDATE aso_file SET status = 1,end_time = {t2} WHERE id ={id}' \
		.format(t2=t2,id=Id)
		try:
				# 执行SQL语句
				cursor1.execute(sql1)
				# 提交到数据库执行
				conn1.commit()
		except:
				# 发生错误时回滚
				conn1.rollback()
		del_file(fileDir)

cursor.close()
conn.close()
cursor1.close()
conn1.close()
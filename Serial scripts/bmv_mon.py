#!/usr/bin/python
# Filename: bmv_mon.py

#Copyright (C) 2014 Jesus Perez <jepefe@gmail.com>
#This program is free software: you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation, either version 2 of the License.
# 
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License at <http://www.gnu.org/licenses/>
#for more details.
import urllib2
import urllib
import httplib
import time
import bmv
from urlparse import urlparse
import os
from optparse import OptionParser
from datetime import datetime
try:
    import json
except ImportError:
    import simplejson as json




def main():
    parser = OptionParser()
    parser.add_option('-p','--port',help='Port to listen',dest='port')
    parser.add_option('-m','--model',help='Use this option if your device is a BMV602 model.',dest='model', action='store_true',default=False)
    parser.add_option('-c','--continuous',help='Print data continuously',dest="continuous",action='store_true',default=False)
    parser.add_option('-i','--interval',help='Time interval between reads in seconds. Use with -c',dest='time_interval',default=0)

    parser.add_option('-j','--json',help='Prints json formatted string with all devices status to stdout',dest='json',\
                      default=False,action='store_true')
    parser.add_option('-n','--datetime',help='Include date and time and send to url. Use with -u.',dest="date_time",action='store_true',default=False)
    parser.add_option('-u','--send-json-url',help='Send json via POST to specified url',dest='url')
    parser.add_option('-t','--token',help='Include security token and send to url. Use with -u.',dest='token')
    (options, args) = parser.parse_args()
    start(options)
   

def start(options):
    
    
    
    
    if options.port:
        if options.model:
            bmvd = bmv.bmv(options.port,602)
        else:
            bmvd = bmv.bmv(options.port,600)
    else:
        print "Port is mandatory"
        return
    
    
    #Prepare connection if send json to url is enabled
    headers = {}
    if options.url:
        headers = {"Content-type": "application/x-www-form-urlencoded","Accept": "text/plain"}
        
    #Set continuous to true for first iteration 
    continuous = True
    
    while continuous:
            
            #Set continuous mode if selected
            continuous = options.continuous
            try:
                bmv_data= bmvd.get_data()
            except:
                print "Error retreiving data, retrying"
            #Send json to url
            if options.url:
                urllist = urlparse(options.url)
                if urllist[0] != 'http':
                    print "Invalid URL, by example http://somewere.com/page.php"
                    return
                else:
                    try:
                        conn = httplib.HTTPConnection(urllist[1])
                        devices_status = "device="+json.dumps(bmv_data) 
                        if options.token:
                            devices_status = devices_status+"&token="+ options.token
                        if options.date_time:
                            devices_status = devices_status+"&datetime="+str(datetime.now())
                        conn.request("POST", urllist[2], devices_status , headers)
                        response = conn.getresponse()
                    except:
                        print 'Network error, retrying'
            
                                  
            
            #Clear screen
           # os.system('cls' if os.name == 'nt' else 'clear')
            
            
            #Print status in json format
            if options.json:
                os.system('cls' if os.name == 'nt' else 'clear')
                print json.dumps(bmv_data)
                
            
                    
            #Set interval
            if options.time_interval > 0:
                for i in range(1,int(options.time_interval)):
                    time.sleep(1)
                    

        
        

if __name__ == '__main__':
    main()

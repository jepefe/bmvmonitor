#----------------------------------#
# Victron BMV battery monitor class#
#----------------------------------#
import serial
from time import sleep
import time
import os
class bmv:
    def __init__(self,serialport,model):

        self.serialport=serialport
        self.valuename=['V','I','CE', 'SOC', 'TTG', "ALARM","RELAY","AR","BMV","FW","CHECKSM",'H1','H2','H3','H4','H5','H6','H7','H8','H9','H10','H11','H12',"CHECKSM"]
        self.valuemod = [0.001,0.001,0.001,0.1,1,1,1,1,1,1,1,0.001,0.001,0.001,1,0.001,0.001,0.001,1,0.00001157407407,1,1,1,1]
        self.ser = serial.Serial(serialport, 19200,timeout=1)
        self.linecountlimit = [24,10,23]
        if model == 602:
            self.valuename=['V','VS0','I','CE', 'SOC', 'TTG', "ALARM","RELAY","AR","BMV","FW","CHECKSM",'H1','H2','H3','H4','H5','H6','H7','H8','H9','H10','H11','H12','H13','H14','H15','H16',"CHECKSM"]
            self.valuemod = [0.001,0.001,0.001,0.001,0.1,1,1,1,1,1,1,1,0.001,0.001,0.001,1,0.001,0.001,0.001,1,0.00001157407407,1,1,1,1,1,1,1,1]
            self.linecountlimit = [29,11,28]

 
 
    def read_data(self):
        linecount=0
        lastlinecount=0
        lastdata = ''
        dataarray = 0
        putvalue=False
        values=[]
        value=''
        data=''
        byteVal=0
        
        counted=0
        self.ser.flushInput()
        i = 0
        while i < 400:  #Bytes read limit before return None
                values=[]
                counter=0
                value=''
                blk1chksum=1
                blk2chksum=1
                i+=1
                
                linecountlimit = self.linecountlimit
                if data=='V' and lastdata=='\n':
                    byteVal=ord('\r') #Sum byte to calculate checksum
                    byteVal+=ord(lastdata)
                    
                    while linecount <linecountlimit[0]:


                        
                        #The received checksum byte is calculated by the BMV
                        #to make the sum of all bytes(included checksum byte) 
                        #divisible by 256

                        #First block checksum
                        if (linecount==linecountlimit[1] and data=='\r') :
                            blk1chksum = byteVal%256 
                            byteVal=0

                        #Second block checksum
                        if (linecount==linecountlimit[2] and data=='\r') :
                            blk2chksum = byteVal%256

                        if len(data)>0:
                            byteVal+=ord(data)

                        if lastdata=='\t':
                            putvalue=True


                        if putvalue and data !='\r' and data!='\n' :
                            value=value+data

                

                        if data == '\n':
                            putvalue=False
                            values.append(value)
                            lastlinecount=linecount
                            linecount +=1
                            value=''
                            if lastlinecount != linecount:
                                    counted = 0
                        
                
                
                                

                        lastdata=data
                        data=self.ser.read(1)


                if linecount==0:
                    lastdata=data
                    data=self.ser.read(1)


                else:
                    linecount=0
                    byteVal=0
                    i=0
                    json={}
                    

                    
                    if not ((blk1chksum  or blk2chksum)): #If correct checksum
                        for i in range(len(self.valuename)-1):
                            if self.valuename[i] not in ['CHECKSM','FW','ALARM','AR','RELAY','BMV']:
                                if str(values[i]).replace('-','').isdigit():
                                    json[self.valuename[i]] = int(values[i])*self.valuemod[i]
                            else:
                                #Dont print checksum due to problems with js
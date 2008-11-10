#include <iostream>
#include <fstream>
#include <string>
#include <sys/stat.h>
#include <mysql.h>
#include "TROOT.h"
#include "TFile.h"
#include "TTree.h"
#include "TSystem.h"
#include "TTimeStamp.h"

using namespace std;

MYSQL *connection, mysql;
MYSQL_RES *result;
MYSQL_ROW row;
int query_state;

void makeLog(int startRun,int endRun);

int main(int argc,char **argv){

  int start=2000;
  int end=5000;

  if(argc>2){
    start=atoi(argv[1]);
    end=atoi(argv[2]);
  }
  makeLog(start,end);

  return 0;
}

void makeLog(int startRun,int endRun){

  char rootFileName[FILENAME_MAX];
  char rawFileName[FILENAME_MAX];
  char queryString[FILENAME_MAX];

  UInt_t realTime;
  UInt_t eventNumber;

  UInt_t firstEvent;
  UInt_t lastEvent;
  UInt_t firstTime;
  UInt_t lastTime;

  UInt_t numEntries;

  string nameLine;
  string dumpLine;
  string logLine;
  string noBeginTime = "NO ROOT FILE";

  string location;

  FileStat_t exist;

  TTimeStamp beginTime;
  TTimeStamp endTime;

  mysql_init(&mysql);
  connection =  mysql_real_connect(&mysql,"localhost","anita","S0uthP0l3","anita",0,0,0);
  if(connection == NULL){
    cout << mysql_error(&mysql);
    return 1;
  }

  for(int run=startRun;run<endRun+1;run++){
    
    sprintf(queryString,"SELECT * FROM runTable WHERE runNumber=run");
    query_state = mysql_query(connection,queryString);
    result = mysql_store_result(connection);
    row = mysql_fetch_row(result);

    //if there is something in the row, and the root data is there then skip over the run
    //if(row!=NULL && row['startTime']  !="NO ROOT FILE") continue;
    if(row!=NULL && row[3]  !="NO ROOT FILE") continue;
    
    ifstream logFile;

    //Get the run log from the raw data directories
    location = "Pal.";
    sprintf(rawFileName,"/unix/anita2/testing/palestine/run%d/log/simpleLog.txt",run);

    struct stat stFileInfo;
    int intStat=0;
    int palDir=0;
    intStat = stat(rawFileName,&stFileInfo);

    if(intStat!=0){
      sprintf(rawFileName,"/unix/anita2/palestine08/raw/run%d/log/simpleLog.txt",run);
      intStat = stat(rawFileName,&stFileInfo);
      if(intStat!=0){
	sprintf(rawFileName,"/unix/anita2/palestine08/raw/run%d/log/simpleLog",run);
      }
      palDir=1;
    }

    intStat = stat(rawFileName,&stFileInfo);

    if(intStat!=0){
      sprintf(rawFileName,"/unix/anita2/testing/uh2008/run%d/log/simpleLog.txt",run);
      location = "UH";
      palDir=0;
    }

    logFile.open(rawFileName);
    

    //if there is a log file then carry on, otherwise go to the next run
    if(logFile){
      getline(logFile,nameLine);
      getline(logFile,dumpLine);
      getline(logFile,logLine);

      logLine.erase (logLine.begin()+0);
      nameLine.erase (nameLine.begin()+0,nameLine.begin()+6);

      logFile.close();

      //Get the event and times from the root file
      if(palDir==0){
	sprintf(rootFileName,"/unix/anita2/testing/rootFiles/run%d/headFile%d.root",run,run);
      }
      else{
	sprintf(rootFileName,"/unix/anita2/palestine08/root/run%d/headFile%d.root",run,run);
      }



      if(!gSystem->GetPathInfo(rootFileName,exist)){
	TFile *rootFile = new TFile(rootFileName);

        TTree *headTree = (TTree*)rootFile->Get("headTree");
	headTree->SetMakeClass(1);
        headTree->SetBranchAddress("realTime",&realTime);
        headTree->SetBranchAddress("eventNumber",&eventNumber);

        numEntries = headTree->GetEntries();

        headTree->GetEntry(0);
        firstTime=realTime;
        firstEvent=eventNumber;

	//cout << firstEvent << " " << realTime << endl;

        headTree->GetEntry(numEntries-1);
        lastTime=realTime;
        lastEvent=eventNumber;

	beginTime.SetSec(firstTime);
	endTime.SetSec(lastTime);

        rootFile->Close();

        //cout << nameLine << endl << logLine << endl;

        //runLogFile << run << "\t" << location << "\t" << beginTime.AsString("s") << "\t" << endTime.AsString("s") << "\t" << firstEvent << "\t" << lastEvent << "\t" << nameLine << "\t" << logLine << endl;
	if(row==NULL){
	  //this means there is no record of the run in the log
	  sprintf(queryString,"INSERT INTO runTable  (id,runNumber,location,startTime,endTime,firstEvent,lastEvent,shifterName,runDescription) VALUES('NULL','run','location','beginTime','endTime','firstEvent','lastEvent','nameLine','logLine')");
	  mysql_query(connection,queryString);
	}
	else{
	  mysql_query(connection,"UPDATE runTable SET startTime='startTime' WHERE runNumber='run'");
	  mysql_query(connection,"UPDATE runTable SET endTime='endTime' WHERE runNumber='run'");
	  mysql_query(connection,"UPDATE runTable SET firstEvent='firstEvent' WHERE runNumber='run'");
	  mysql_query(connection,"UPDATE runTable SET lastEvent='lastEvent' WHERE runNumber='run'");
	}

      }
      else{
	//runLogFile << run << "\t" << location  << "\tNO ROOT FILE" << "\t\t\t\t" << nameLine << "\t" << logLine << endl;
	sprintf(queryString,"INSERT INTO runTable  (id,runNumber,location,startTime,shifterName,runDescription) VALUES('NULL','run','location','noBeginTime','nameLine','logLine')");
	mysql_query(connection,queryString);
      }

    

    }//if
    
  }//run

  runLogFile.close();

}  

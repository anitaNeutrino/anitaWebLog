#include <iostream>
#include <fstream>
#include <string>
#include <sys/stat.h>
#include <mysql/mysql.h>
#include "TROOT.h"
#include "TFile.h"
#include "TTree.h"
#include "TSystem.h"
#include "TTimeStamp.h"
#include <algorithm>
#include <cstdlib>

using namespace std;

MYSQL *connection, mysql;
MYSQL_RES *result;
MYSQL_ROW row;
int query_state;

/* Globals for the lazy */
const char* antarctica16Dir = "/data/antarctica2016/";
const char* palestine16Dir = "/data/palestine2016/";
const char* logFilePath = "/home/anita/anitaWebLog/runLog/runLog.txt";

int makeEntries(int startRun, int endRun);
int lookForFile(const char* fileName);
int lookForRootFile(const char* rootFileName, int run);
string escape(const string& s, const char* toEscapePtr, const char* escapeByPtr);

int main(int argc,char **argv){

  int start=1000;
  int end=2000;

  if(argc>2){
    start=atoi(argv[1]);
    end=atoi(argv[2]);
  }

  return makeEntries(start, end);
}

int makeEntries(int startRun, int endRun){
  char rootFileName[FILENAME_MAX];
  char rawFileName[FILENAME_MAX];
  char queryString[FILENAME_MAX];

  string nameLine;
  string dumpLine;
  string logLine;

  string location;

  mysql_init(&mysql);
  connection =  mysql_real_connect(&mysql,"localhost","anita","IceRadi0","runLog",0,0,0);

  if(connection == NULL){
    fprintf(stderr, "Error connecting to sql server!\n");
    cout << mysql_error(&mysql) << endl;
    return 1;
  }

  /* Now let's go after the data */
  for(int run=startRun;run<endRun+1;run++){
    
    ifstream logFile;

    //Get the run log from the raw data directories

    /* Try antarctica first */
    location = "Antarctica";
    sprintf(rawFileName,"%s/raw/run%d/log/simpleLog.txt",antarctica16Dir, run);

    int palDir = 0;

    int intStat = lookForFile(rawFileName);

    /* Then try Palestine */
    if(intStat!=0){\
      location = "Palestine";
      sprintf(rawFileName,"%s/raw/run%d/log/simpleLog.txt",palestine16Dir,run);
      intStat = lookForFile(rawFileName);
      palDir=1;
    }

    logFile.open(rawFileName);

    // If there is a log file then carry on, otherwise go to the next run.
    if(logFile){
      printf("Found log file for run %d at %s\n", run, rawFileName);

      getline(logFile,nameLine);
      getline(logFile,dumpLine);
      getline(logFile,logLine);

      if(nameLine.size() > 6){
	/* Cut out input prompts from simpleLog for nice output log file. */
	logLine.erase (logLine.begin()+0);
	nameLine.erase (nameLine.begin()+0,nameLine.begin()+6);
      }
      else{
	fprintf(stderr, "Something dodgy trying to read from log file for run %d at %s\n", run, rawFileName);
	logFile.close();
	continue;
      }

      // Fucking sql piece of shit... you have to escape or remove all this bollocks...
      replace(nameLine.begin(), nameLine.end(), '\\' , ' ');
      replace(logLine.begin(), logLine.end(), '\\' , ' ');
      nameLine = escape(nameLine, "'", "'");
      logLine = escape(logLine, "'", "'");
      
      logFile.close();

      //Get the event and times from the root file
      if(palDir==0){
	sprintf(rootFileName,"%s/root/run%d/headFile%d.root",\
		antarctica16Dir,run,run);
      }
      else{
	sprintf(rootFileName,"%s/root/run%d/headFile%d.root",
		palestine16Dir,run,run);
      }



      sprintf(queryString,"SELECT * FROM runTable WHERE run=%d", run);
      query_state = mysql_query(connection,queryString);
      result = mysql_store_result(connection);
      row = mysql_fetch_row(result);
      
      if(row==NULL){
	/* Add everything from the log File*/      
	//printf("No row matching. Reading in log file information for run %d\n", run);
	sprintf(queryString, "insert into runTable values ('%d', '%s', 'NO ROOT FILE', '', '', '', '%s', '%s', '', '')", run, location.c_str(), nameLine.c_str(), logLine.c_str());
	
	int retVal = mysql_query(connection,queryString);
	if(retVal != 0){
	  fprintf(stderr, "Something went wrong processing query %s\n", queryString);
	}
      }
      else if(strcmp(row[2],"Of course not")==0){
	/* Try to add ROOT information from the file */
	//printf("row[2] = %s\n", row[2]);
      }
      else{
	/* Take no action! */
	continue; /* i.e. skip looking for ROOT file! */
      }
      
      
      /* Here we look for the ROOT file */
      sprintf(rootFileName,"%s/root/run%d/headFile%d.root",antarctica16Dir,run,run);
      
      if(lookForRootFile(rootFileName, run)!=0){
	sprintf(rootFileName,"%s/root/run%d/headFile%d.root",palestine16Dir,run,run);
	lookForRootFile(rootFileName, run);
	std::cout << rootFileName << "\n";
	fprintf(stderr, "Unable to locate ROOT file in either %s or %s.\n", antarctica16Dir, palestine16Dir);
      }

      logFile.close();
      
    } // if logFile()

    
  } // loop over runs

  return 0;

}  

int lookForFile(const char* fileName){
  struct stat stFileInfo;
  return stat(fileName,&stFileInfo);
}



int lookForRootFile(const char* rootFileName, int run){
  UInt_t realTime;
  UInt_t eventNumber;

  UInt_t firstEvent;
  UInt_t lastEvent;
  UInt_t firstTime;
  UInt_t lastTime;

  UInt_t numEntries;

  FileStat_t exist;

  TTimeStamp beginTime;
  TTimeStamp endTime;
  
  char queryString[1024];


  if(!gSystem->GetPathInfo(rootFileName,exist)){
    //    printf("Hey, I found a header file for run %d\n", run);
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
    
    /* Update the SQL thingies */
    const char* startTimeString = beginTime.AsString("s");
    sprintf(queryString, "UPDATE runTable SET startTime='%s' WHERE run='%d'", startTimeString, run);
    mysql_query(connection, queryString);
    
    const char* endTimeString = endTime.AsString("s");
    sprintf(queryString, "UPDATE runTable SET endTime='%s' WHERE run='%d'", endTimeString, run);
    mysql_query(connection, queryString);
    
	
    sprintf(queryString, "UPDATE runTable SET firstEvent='%d' WHERE run='%d'", firstEvent, run);
    mysql_query(connection, queryString);
    
    sprintf(queryString, "UPDATE runTable SET lastEvent='%d' WHERE run='%d'", lastEvent, run);
    mysql_query(connection, queryString);      


    return 0;
    
  }      
  else{
    //    fprintf(stderr, "Unable to find ROOT header file at %s for run %d", rootFileName, run);
    return 1;
  }
  
}


string escape(const std::string& s, const char* toEscapePtr, const char* escapeByPtr){
  /* Currently only does single chars */
  
  int n = s.size(), wp = 0;
  vector<char> result(n*2);
  for (int i=0; i<n; i++)
    {
      if (s[i] == toEscapePtr[0])
	result[wp++] = escapeByPtr[0];
      result[wp++] = s[i];
    }
  return string(&result[0], &result[wp]);
}


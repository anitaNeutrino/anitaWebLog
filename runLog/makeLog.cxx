#include <iostream>
#include <fstream>
#include <string>
#include <sys/stat.h>
#include "TROOT.h"
#include "TFile.h"
#include "TTree.h"
#include "TSystem.h"
#include "TTimeStamp.h"

#include <cstdlib>

using namespace std;


/* Globals for the lazy */
const char* antarctica16Dir = "/anitaStorage/antarctica2016/";
const char* palestine14Dir = "/anitaStorage/palestine14/";
const char* palestine16Dir = "/data/palestine2016/";
const char* logFilePath = "/home/anita/Code/anitaWebLog/runLog/runLog.txt";



void makeLog(int startRun,int endRun);
int lookForFile(const char* fileName); /* 0 on success */

int main(int argc,char **argv){

  int start=1000;
  int end=2000;

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

  string location;

  FileStat_t exist;

  TTimeStamp beginTime;
  TTimeStamp endTime;

  ofstream runLogFile(logFilePath);\
  /* Print header info */
  runLogFile << "RUN\tLoc\tStart Time\t\tEnd Time\t\t1st Ev\tLast Ev\tUser\tEvent Description" << endl;

  /* Now let's go after the data */
  for(int run=startRun;run<endRun+1;run++){
    
    ifstream logFile;

    //Get the run log from the raw data directories
    location = "Antarctica";
    sprintf(rawFileName,"%s/raw/run%d/log/simpleLog.txt",antarctica16Dir, run);

    int palDir = 0;

    int intStat = lookForFile(rawFileName);

    if(intStat!=0){\
      location = "Palestine";
      sprintf(rawFileName,"%s/raw/run%d/log/simpleLog.txt",palestine16Dir,run);
      intStat = lookForFile(rawFileName);
      palDir=1;
    }

    logFile.open(rawFileName);

    // If there is a log file then carry on, otherwise go to the next run.
    if(logFile){
      getline(logFile,nameLine);
      getline(logFile,dumpLine);
      getline(logFile,logLine);

      /* Cut out input prompts from simpleLog for nice output log file. */
      logLine.erase (logLine.begin()+0);
      nameLine.erase (nameLine.begin()+0,nameLine.begin()+6);

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

      /* Now look for ROOT files */
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

        headTree->GetEntry(numEntries-1);
        lastTime=realTime;
        lastEvent=eventNumber;

	beginTime.SetSec(firstTime);
	endTime.SetSec(lastTime);

        rootFile->Close();

        runLogFile << run << "\t" << location << "\t" << beginTime.AsString("s") << "\t" << endTime.AsString("s") << "\t" << firstEvent << "\t" << lastEvent << "\t" << nameLine << "\t" << logLine << endl;

      }
      else{
	runLogFile << run << "\t" << location  << "\tNO ROOT FILE" << "\t\t\t\t" << nameLine << "\t" << logLine << endl;
      }

    

    } // if logFile()
    
  } // loop over runs

  runLogFile.close();
}  

int lookForFile(const char* fileName){
  struct stat stFileInfo;
  return stat(fileName,&stFileInfo);
}

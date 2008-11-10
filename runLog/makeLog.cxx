#include <iostream>
#include <fstream>
#include <string>
#include <sys/stat.h>
#include "TROOT.h"
#include "TFile.h"
#include "TTree.h"
#include "TSystem.h"
#include "TTimeStamp.h"

using namespace std;

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
  //char rootFileName[70];
  //char rawFileName[70];

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

  //ofstream runLogFile("/home/mottram/work/anitaLog/runLog2/runLog.txt");
  ofstream runLogFile("/unix/www/users/mottram/public_html/anitaLog/runLog/runLog.txt");

  //runLogFile << "RUN\tLoc\tStart Time\t\tEnd Time\t\t1st Ev\tLast Ev\tUser\tEvent Description" << endl;

  for(int run=startRun;run<endRun+1;run++){
    
    //cout << "on run " << run << endl;

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

        runLogFile << run << "\t" << location << "\t" << beginTime.AsString("s") << "\t" << endTime.AsString("s") << "\t" << firstEvent << "\t" << lastEvent << "\t" << nameLine << "\t" << logLine << endl;

      }
      else{
	runLogFile << run << "\t" << location  << "\tNO ROOT FILE" << "\t\t\t\t" << nameLine << "\t" << logLine << endl;
      }

    

    }//else
    
  }//run

  runLogFile.close();

}  

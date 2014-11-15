/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"
#include <iostream>

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
	treeHeight = 0; //On creation of BTreeIndex, set height to zero; there are no entries at all yet
	
	//Buffer will communicate with rootPid and treeHeight inside open and close methods 
	std::fill(buffer, buffer + PageFile::PAGE_SIZE, 0); //clear the buffer if necessary
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
	//Use PageFile's open method with same parameters
    RC error = pf.open(indexname, mode);
	
	if(error!=0)
		return error;
	
	//Initialize the variables again (if opened for the first time) just in case
	if(pf.endPid()==0)
	{
		rootPid = -1;
		treeHeight = 0;
		
		//Disk must be writeable also; otherwise, we have an error
		RC error = pf.write(0, buffer);
		if(error!=0)
			return error;
		
		return 0;
	}
	
	//Otherwise, read in buffer's data from disk saved in pid=0 PageFile
	error = pf.read(0, buffer);

	if(error!=0)
		return error;
	
	//Find values in buffer that might have saved rootPid or treeHeight
	int tempPid;
	int tempHeight;
	memcpy(&tempPid, buffer, sizeof(int));
	memcpy(&tempHeight, buffer+4, sizeof(int));
	
	//Only set rootPid and treeHeight to what is saved on disk if it makes sense
	//rootPid cannot be 0, since that is where we are saving our buffer page
	//rootPid also cannot be negative
	//Set tempHeight to what is saved on disk if it is positive
	if(tempPid>0 && tempHeight>=0)
	{
		rootPid = tempPid;
		treeHeight = tempHeight;
	}
	
	//Otherwise, we'll stick with their default constructor values and return success
	return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
	//Before closing the PageFile, save variables to buffer
	memcpy(buffer, &rootPid, sizeof(int));
	memcpy(buffer+4, &treeHeight, sizeof(int));
	
	//Then write buffer into PageFile
	RC error = pf.write(0, buffer);
	
	if(error!=0)
		return error;

	//Use PageFile's close method
    return pf.close();
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{	
	RC error;

    //Keys are assumed to be non-zero, but we can check it anyway
	if(key<0)
		return RC_INVALID_ATTRIBUTE;
	
	//If tree doesn't exist yet, we simply need to start a new tree (root)
	if(treeHeight==0)
	{
		//Create a new leaf node with inserted value to act as root
		BTLeafNode newTree;
		newTree.insert(key, rid);
		
		//Update BTree's root pid
		//If endPid is zero (file just created), set it to 1 so the first PageId can be accessed
		//We want rootPid to start at 1 so we can use the PageFile at pid=0 for storing private data
		if(pf.endPid()==0)
			rootPid = 1;
		else
			rootPid = pf.endPid();
		
		//If insert successful, increment tree height
		treeHeight++;
		
		//Write tree into specified pid in PageFile
		return newTree.write(rootPid, pf);
	}
	
	//Otherwise, we'll have to traverse the tree and insert where possible
	//Again, start with pid at 1, since we won't insert at a pid of 0
	//Current height also starts at 1, since we should have a root by now
	
	//These variables will be used to store keys that must be inserted in higher level nodes
	int insertKey = -1;
	PageId insertPid = -1;
	
	error = insertRec(key, rid, 1, rootPid, insertKey, insertPid);
	
	if(error!=0)
		return error;
	
	return 0;
}

//Recursive function for inserting key into correct leaf and non-leaf nodes alike
RC BTreeIndex::insertRec(int key, const RecordId& rid, int currHeight, PageId thisPid, int& tempKey, PageId& tempPid)
{
	//If anything breaks along the way, return as error
	RC error;
	
	//These variables may be used later to facilitate splitting and parent inserting
	//Between different levels of recursion
	tempKey = -1;
	tempPid = -1;
	
	//We start at a height of 1
	//If we reach the tree's max height, we can simply add a leaf node
	if(currHeight==treeHeight)
	{
		//Generate "new" leaf node and read contents over
		BTLeafNode thisLeaf;
		thisLeaf.read(thisPid, pf);

		//Try inserting leaf node
		//If succesful, write back into PageFile and return
		if(thisLeaf.insert(key, rid)==0)
		{	
			thisLeaf.write(thisPid, pf);
			return 0;
		}

		//At this point, insert was not successful (likely due to overflow)
		//Try inserting leaf node via splitting
		BTLeafNode anotherLeaf;
		int anotherKey;
		error = thisLeaf.insertAndSplit(key, rid, anotherLeaf, anotherKey);
		
		if(error!=0)
			return error;
		
		//Right now, anotherKey is the median key that needs to be placed into parent
		//We'll utilize the parameters for that purpose
		int lastPid = pf.endPid();
		tempKey = anotherKey;
		tempPid = lastPid;

		//Write new contents into thisLeaf and anotherLeaf
		anotherLeaf.setNextNodePtr(thisLeaf.getNextNodePtr());
		thisLeaf.setNextNodePtr(lastPid);

		//Notice that anotherLeaf starts writing at the end of the last pid
		//The node anotherLeaf gets tacked on to the end of the PageFile, incrementing endPid()
		error = anotherLeaf.write(lastPid, pf);
		
		if(error!=0)
			return error;
		
		error = thisLeaf.write(thisPid, pf);
		
		if(error!=0)
			return error;
		
		//If we just split a root, we'll now need a new single non-leaf node
		//The new first value of the sibling node (anotherLeaf) gets inserted into root
		if(treeHeight==1)
		{
			//We create a root that has pid pointers to both the new children (which we just split)
			//The new root's value is anotherKey, which is the median key that was pushed up in split
			BTNonLeafNode newRoot;
			newRoot.initializeRoot(thisPid, anotherKey, lastPid);
			treeHeight++;
			
			//Update the rootPid, then write into the PageFile and return
			rootPid = pf.endPid();
			newRoot.write(rootPid, pf);
		}
		
		return 0;
	}
	else
	{
		//Otherwise, we're still somewhere in the middle of the tree
		BTNonLeafNode midNode;
		midNode.read(thisPid, pf);
		
		//Since we're in the middle, we find the corresponding child node for key
		PageId childPid = -1;
		midNode.locateChildPtr(key, childPid);
		
		int insertKey = -1;
		PageId insertPid = -1;
		
		//Recursive part: keep traversing through the tree, inserting at the next node closer to leaf
		error = insertRec(key, rid, currHeight+1, childPid, insertKey, insertPid);
		
		//Error in inserting to node due to reaching full capacity
		//We split the level below to make more space
		//if(error!=0)
		//This means some node was split, and we'll have to add a median key to the parent node
		if(!(insertKey==-1 && insertPid==-1)) 
		{
			if(midNode.insert(insertKey, insertPid)==0)
			{
				//If we were able to successfully insert the child's median key into midNode
				//Write it into PageFile
				midNode.write(thisPid, pf);
				return 0;
			}
			
			//Otherwise, this level had no space either (insert was not successful; overflow)
			//We'll have to insert and split again and propagate the median key upwards to the next parent
			BTNonLeafNode anotherMidNode;
			int anotherKey;
			
			midNode.insertAndSplit(insertKey, insertPid, anotherMidNode, anotherKey);
			
			//As before, even if we split in a nonleaf node, we'll still need its median key for the parent
			//Update the parameters
			int lastPid = pf.endPid();
			tempKey = anotherKey;
			tempPid = lastPid;
			
			//Write new contents into midNode and anotherMidNode
			error = midNode.write(thisPid, pf);
			
			if(error!=0)
				return error;
			
			error = anotherMidNode.write(lastPid, pf);
			
			if(error!=0)
				return error;
			
			//If we just split a root, we'll now need a new single non-leaf node
			//The new first value of the sibling node (anotherMidNode) gets inserted into root
			if(treeHeight==1)
			{
				//We create a root that has pid pointers to both the new children (which we just split)
				//The new root's value is anotherKey, which is the median key that was pushed up in split
				BTNonLeafNode newRoot;
				newRoot.initializeRoot(thisPid, anotherKey, lastPid);
				treeHeight++;
				
				//Update the rootPid, then write into the PageFile and return
				rootPid = pf.endPid();
				newRoot.write(rootPid, pf);
			}
			
		}
		return 0;
	}
}

/*
 * Find the leaf-node index entry whose key value is larger than or 
 * equal to searchKey, and output the location of the entry in IndexCursor.
 * IndexCursor is a "pointer" to a B+tree leaf-node entry consisting of
 * the PageId of the node and the SlotID of the index entry.
 * Note that, for range queries, we need to scan the B+tree leaf nodes.
 * For example, if the query is "key > 1000", we should scan the leaf
 * nodes starting with the key value 1000. For this reason,
 * it is better to return the location of the leaf node entry 
 * for a given searchKey, instead of returning the RecordId
 * associated with the searchKey directly.
 * Once the location of the index entry is identified and returned 
 * from this function, you should call readForward() to retrieve the
 * actual (key, rid) pair from the index.
 * @param key[IN] the key to find.
 * @param cursor[OUT] the cursor pointing to the first index entry
 *                    with the key value.
 * @return error code. 0 if no error.
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
	RC error;	
	BTNonLeafNode midNode;
	BTLeafNode leaf;
	
	int eid;
	int currHeight = 1;
	PageId nextPid = rootPid;
	
	while(currHeight!=treeHeight)
	{
		error = midNode.read(nextPid, pf);
		
		if(error!=0)
			return error;
		
		//Locate child node to look at next given the search key; update nextPid
		error = midNode.locateChildPtr(searchKey, nextPid);
		
		if(error!=0)
			return error;
		
		currHeight++;
	}
	
	error = leaf.read(nextPid, pf);
		
	if(error!=0)
		return error;
	
	//Locate leaf node that corresponds with search key; update eid
	error = leaf.locate(searchKey, eid);
	
	if(error!=0)
		return error;
	
	//Set up the IndexCursor with the found eid and nextPid (which is now current pid)
	cursor.eid = eid;
	cursor.pid = nextPid;
	
	return 0;
	
	//Try using recursive algorithm
	//The currentHeight starts at 1 (the root) and the page index starts at rootPid
    //return locateRec(searchKey, cursor, 1, rootPid);
}

//Recursive function for determining location where a search key belongs
//Runs until we hit the base case of finding the search key's corresponding leaf node
RC BTreeIndex::locateRec(int searchKey, IndexCursor& cursor, int currHeight, PageId& nextPid)
{
	//Keys are assumed to be non-zero, but we can check it anyway
	if(searchKey<0)
		return RC_INVALID_ATTRIBUTE;
		
	//If anything breaks along the way, return as error
	RC error;
	
	if(currHeight==treeHeight) //Base case when we reach the leaf node (found position for searchKey)
	{
		//Initialize eid for returning
		int eid = -1;
	
		//Load data for the leaf
		BTLeafNode leaf;
		error = leaf.read(nextPid, pf);
		
		if(error!=0)
			return error;
		
		//Locate leaf node that corresponds with search key; update eid
		error = leaf.locate(searchKey, eid);
		
		if(error!=0)
			return error;
		
		//Set up the IndexCursor with the found eid and nextPid (which is now current pid)
		cursor.eid = eid;
		cursor.pid = nextPid;
		
		return 0;
	}
	
	//Otherwise, we're still stuck in a non-leaf node; load data for that middle node
	BTNonLeafNode midNode;
	error = midNode.read(nextPid, pf);
	
	if(error!=0)
		return error;
	
	//Locate child node to look at next given the search key; update nextPid
	error = midNode.locateChildPtr(searchKey, nextPid);
	
	if(error!=0)
		return error;
	
	//Try locate again recursively in order to reach the correct leaf node (base case)
	return locateRec(searchKey, cursor, currHeight-1, nextPid);
}

/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */ 
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
	//If anything breaks along the way, return as error
	RC error;

	//Grab position details from the cursor parameter
	PageId cursorPid = cursor.pid;
	int cursorEid = cursor.eid;
	
	//Load data for the cursor's leaf
	BTLeafNode leaf;
	error = leaf.read(cursorPid, pf);
	
	if(error!=0)
		return error;
	
	//Based on the cursor's eid, find return the key and rid
	error = leaf.readEntry(cursorEid, key, rid);
	
	if(error!=0)
		return error;
		
	//the cursor's PageId should never go beyond an uninitialized page
	if(cursorPid <= 0)
		return RC_INVALID_CURSOR;
	
	//Now we need to increment the cursorEid
	//Be careful that the cursorEid does not exceed the max index of the leaf's buffer
	
	//Check that incrementing the cursorEid would not exceed the maximum eid index as determined by key count
	if(cursorEid+1 >= leaf.getKeyCount())
	{
		//If we exceed eid bounds, reset cursor's eid to zero
		//Instead, increment the pid
		cursorEid = 0;
		cursorPid = leaf.getNextNodePtr();
	}
	else
		cursorEid++;
	
	//Write the new position back into cursor parameter
	cursor.eid = cursorEid;
	cursor.pid = cursorPid;
	return 0;
}

PageId BTreeIndex::getRootPid()
{
	return rootPid;
}

int BTreeIndex::getTreeHeight()
{
	return treeHeight;
}

//this function only prints up to two levels of nodes
//BTreeNode buffers must be public and code below must be uncommented for successful print
void BTreeIndex::print()
{
/*
	if(treeHeight==1)
	{	
		BTLeafNode root;
		root.read(rootPid, pf);
		root.print();
	}
	else if(treeHeight>1)
	{
		BTNonLeafNode root;
		root.read(rootPid, pf);
		root.print();
		
		PageId first, rest;
		memcpy(&first, root.buffer, sizeof(PageId));

		BTLeafNode firstLeaf, leaf;
		firstLeaf.read(first, pf);
		firstLeaf.print();
		
		//print the rest of the leaf nodes
		for(int i=0; i<root.getKeyCount(); i++)
		{
			memcpy(&rest, root.buffer+12+(8*i), sizeof(PageId));
			leaf.read(rest, pf);
			leaf.print();
		}
		
		//print each leaf node's current pid and next pid
		cout << "----------" << endl;
		
		for(int i=0; i<root.getKeyCount(); i++)
		{
			if(i==0)
				cout << "leaf0 (pid=" << first << ") has next pid: " << firstLeaf.getNextNodePtr() << endl;
		
			BTLeafNode tempLeaf;
			PageId tempPid;
			memcpy(&tempPid, root.buffer+12+(8*i), sizeof(PageId));
		
			tempLeaf.read(tempPid, pf);;
			
			cout << "leaf" << i+1 << " (pid=" << tempPid << ") has next pid: " << tempLeaf.getNextNodePtr() << endl;
		}
	}	
*/
}

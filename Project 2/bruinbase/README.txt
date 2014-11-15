Name: Nathan Tung
SID: 004-059-195
Email: nathanctung@ucla.edu

Project done without a partner (took a very long time...)! Please use my grace days (I have not used any yet).

INFORMATION ON PART D:
I believe everything so far follows the specs quite carefully. 
I implemented Part D's select by using a series of switches, if-statements, and some goto-statements to filter through the conditions.
The design was based off of the the original select implementation in the given code.
In addition, I calculated bounds on keys (and values, though to a lesser extent) to determine when no query results would show.
In such a scenario, we would end the query immediately, saving both time and page reads.
Lastly, the load function is rather self-explanatory. I basically did a conditional on index and created a B+ Tree for indexing.

INFORMATION ON PREVIOUS PARTS:
I also fixed the next node pointer assignments in both Part B and Part C. I also made some other minor changes to improve correctness.
Comments may have been altered or added in previous parts for clarity of the code.
All the test cases given to us now pass. I also wrote a quick print function for showing the B+ Tree's keys/structure.
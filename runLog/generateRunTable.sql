use runLog
drop table if exists runTable
CREATE TABLE runTable (run int, location text, startTime text, endTime text, firstEvent text, lastEvent text, user text, description longtext, commenter text, comments longtext)

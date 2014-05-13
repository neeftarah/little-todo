CREATE TABLE projects (
   id          INTEGER PRIMARY KEY,
   name        VARCHAR(255),
   orderno     INTEGER
);
CREATE TABLE tasks (
   id          INTEGER PRIMARY KEY,
   project_id  INTEGER,
   title       VARCHAR(255),
   orderno     INTEGER,
   priority    INTEGER,
   deadline    DATE,
   is_finished BOOLEAN,
   FOREIGN KEY(project_id) REFERENCES projects(id)
);
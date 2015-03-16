CREATE TABLE projects (
   id          INTEGER PRIMARY KEY,
   name        VARCHAR(255),
   orderno     INTEGER DEFAULT 1
);
CREATE TABLE tasks (
   id          INTEGER PRIMARY KEY,
   project_id  INTEGER NOT NULL,
   title       VARCHAR(255),
   orderno     INTEGER DEFAULT 1,
   priority    INTEGER DEFAULT 0,
   deadline    DATE,
   is_finished BOOLEAN NOT NULL DEFAULT 0,
   FOREIGN KEY(project_id) REFERENCES projects(id)
);

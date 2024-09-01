DROP TABLE IF EXISTS "api_tokens";

CREATE TABLE IF NOT EXISTS "api_tokens" (
	"id"	INTEGER,
	"token"	TEXT NOT NULL,
	"is_super_token" INTEGER,
	PRIMARY KEY("id" AUTOINCREMENT)
);

INSERT INTO "api_tokens" VALUES (1,"L1K3%=C0Nd_1nj3ct10N!", 1);

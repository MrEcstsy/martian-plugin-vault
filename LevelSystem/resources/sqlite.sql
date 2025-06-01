-- #!sqlite
-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    plevel INT DEFAULT 0
    );
-- #  }

-- #  { select
SELECT *
FROM players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :plevel int
INSERT OR REPLACE INTO players(uuid, username, plevel)
VALUES (:uuid, :username, :plevel);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :plevel int
UPDATE players
SET username=:username,
    plevel=:plevel
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM players
WHERE uuid=:uuid;
-- #  }
-- # }
-- #!sqlite
-- # { orewatch_players
-- #  { initialize
CREATE TABLE IF NOT EXISTS orewatch_players (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    notify INT DEFAULT 0
    );
-- #  }

-- #  { select
SELECT *
FROM orewatch_players;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :notify int
INSERT OR REPLACE INTO orewatch_players(uuid, username, notify)
VALUES (:uuid, :username, :notify);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :notify int
UPDATE orewatch_players
SET username=:username,
    notify=:notify
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM orewatch_players
WHERE uuid=:uuid;
-- #  }
-- # }
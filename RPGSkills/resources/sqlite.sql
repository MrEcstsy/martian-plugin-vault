-- #!sqlite
-- # { players
-- #  { initialize
CREATE TABLE IF NOT EXISTS players_rpgskills (
    uuid VARCHAR(36) PRIMARY KEY,
    username VARCHAR(16),
    mining_level INT DEFAULT 0,
    attack_level INT DEFAULT 0,
    farming_level INT DEFAULT 0,
    gathering_level INT DEFAULT 0,
    defense_level INT DEFAULT 0,
    magic_level INT DEFAULT 0,
    building_level INT DEFAULT 0,
    agility_level INT DEFAULT 0
    );
-- #  }

-- #  { select
SELECT *
FROM players_rpgskills;
-- #  }

-- #  { create
-- #      :uuid string
-- #      :username string
-- #      :mining_level int
-- #      :attack_level int
-- #      :farming_level int
-- #      :gathering_level int
-- #      :defense_level int
-- #      :magic_level int
-- #      :building_level int
-- #      :agility_level int
INSERT OR REPLACE INTO players_rpgskills(uuid, username, mining_level, attack_level, farming_level, gathering_level, defense_level, magic_level, building_level, agility_level)
VALUES (:uuid, :username, :mining_level, :attack_level, :farming_level, :gathering_level, :defense_level, :magic_level, :building_level, :agility_level);
-- #  }

-- #  { update
-- #      :uuid string
-- #      :username string
-- #      :mining_level int
-- #      :attack_level int
-- #      :farming_level int
-- #      :gathering_level int
-- #      :defense_level int
-- #      :magic_level int
-- #      :building_level int
-- #      :agility_level int
UPDATE players_rpgskills
SET username=:username,
    mining_level=:mining_level,
    attack_level=:attack_level,
    farming_level=:farming_level,
    gathering_level=:gathering_level,
    defense_level=:defense_level,
    magic_level=:magic_level,
    building_level=:building_level,
    agility_level=:agility_level
WHERE uuid=:uuid;
-- #  }

-- #  { delete
-- #      :uuid string
DELETE FROM players_rpgskills
WHERE uuid=:uuid;
-- #  }
-- # }
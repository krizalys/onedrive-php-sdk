<?php

namespace Krizalys\Onedrive;

class NameConflictBehavior
{
    // Fail behavior: fail the operation if the object exists.
    const FAIL = 1;

    // Rename behavior: rename the object if it already exists. The object is
    // renamed as "<original name> 1", incrementing the trailing number until
    // an available file name is discovered.
    const RENAME = 2;

    // Replace behavior: replace the object if it already exists.
    const REPLACE = 3;
}

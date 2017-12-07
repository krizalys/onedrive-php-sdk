<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Krizalys\Onedrive\NameConflictBehavior;
?>
<!DOCTYPE html>
<html lang=en dir=ltr>
    <head>
        <meta charset=utf-8>
        <title>Demonstration of the OneDrive SDK for PHP</title>
        <link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap.min.css>
        <link rel=stylesheet href=//ajax.aspnetcdn.com/ajax/bootstrap/3.2.0/css/bootstrap-theme.min.css>
        <meta name=viewport content="width=device-width, initial-scale=1">
    </head>
    <body>
        <div class=container>
            <h1>Demonstration of the OneDrive SDK for PHP</h1>
            <form action=root.php>
                <fieldset>
                    <legend>Fetching the OneDrive root</legend>
                    <p>Fetches the OneDrive account's root using the <code>Client::fetchRoot</code> method.</p>
                    <p><input type=submit value="Fetch root" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=folder.php>
                <fieldset>
                    <legend>Fetching a OneDrive folder</legend>
                    <p>Fetches a folder from the OneDrive account using the <code>Client::fetchDriveItem</code> method.</p>
                    <div class=form-group>
                        <label for=folder-id>ID:</label>
                        <input name=id id="folder-id" placeholder=ID class=form-control>
                    </div>
                    <p><input type=submit value="Fetch folder" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=file.php>
                <fieldset>
                    <legend>Fetching a OneDrive file</legend>
                    <p>Fetches a file from the OneDrive account using the <code>Client::fetchDriveItem</code> method.</p>
                    <div class=form-group>
                        <label for=file-id>ID:</label>
                        <input name=id id="file-id" placeholder=ID class=form-control>
                    </div>
                    <p><input type=submit value="Fetch file" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=create-folder.php>
                <fieldset>
                    <legend>Creating a OneDrive folder</legend>
                    <p>Creates a folder in the OneDrive account using the <code>Folder::createFolder</code> method.</p>
                    <div class=form-group>
                        <label for=create-folder-parent-id>Parent ID:</label>
                        <input name=parent_id id="create-folder-parent-id" placeholder="Parent ID" class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=create-folder-name>Name:</label>
                        <input name=name id="create-folder-name" placeholder=Name class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=create-folder-description>Description:</label>
                        <textarea name=description id="create-folder-description" placeholder=Description class=form-control></textarea>
                    </div>
                    <p><input type=submit value="Create folder" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=create-file.php>
                <fieldset>
                    <legend>Creating a OneDrive file</legend>
                    <p>Creates a file in the OneDrive account using the <code>Folder::createFile</code> method.</p>
                    <div class=form-group>
                        <label for=create-file-parent-id>Parent ID:</label>
                        <input name=parent_id id="create-file-parent-id" placeholder="Parent ID" class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=create-file-name>Name:</label>
                        <input name=name id="create-file-name" placeholder=Name class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=create-file-content>Content:</label>
                        <textarea name=content id="create-file-content" placeholder=Content class=form-control></textarea>
                    </div>
                    <div class=form-group>
                        <fieldset>
                            <legend>Name conflict behavior</legend>
                            <div>
                                <label><input name=name_conflict_behavior type=radio value="<?php echo NameConflictBehavior::REPLACE ?>" checked> <code>REPLACE</code></label>
                            </div>
                            <div>
                                <label><input name=name_conflict_behavior type=radio value="<?php echo NameConflictBehavior::RENAME ?>"> <code>RENAME</code></label>
                            </div>
                            <div>
                                <label><input name=name_conflict_behavior type=radio value="<?php echo NameConflictBehavior::FAIL ?>"> <code>FAIL</code></label>
                            </div>
                        </fieldset>
                    </div>
                    <p><input type=submit value="Create file" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=update-drive-item.php>
                <fieldset>
                    <legend>Updating a OneDrive drive item</legend>
                    <p>Updates a drive item in the OneDrive account using the <code>Client::updateDriveItem</code> method. Updating drive items does not modify their content (for a file) or their child drive items (for a folder).</p>
                    <div class=form-group>
                        <label for=update-drive-item-id>ID:</label>
                        <input name=id id="update-drive-item-id" placeholder=ID class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=update-drive-item-name>Name:</label>
                        <input name=name id="update-drive-item-name" placeholder=Name class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=update-drive-item-description>Description:</label>
                        <textarea name=description id="update-drive-item-description" placeholder=Description class=form-control></textarea>
                    </div>
                    <p><input type=submit value="Update drive item" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=delete-drive-item.php>
                <fieldset>
                    <legend>Deleting a OneDrive drive item</legend>
                    <p>Deletes a drive item from the OneDrive account using the <code>Client::deleteDriveItem</code> method.</p>
                    <div class=form-group>
                        <label for=delete-drive-item-id>ID:</label>
                        <input name=id id="delete-drive-item-id" placeholder=ID class=form-control>
                    </div>
                    <p><input type=submit value="Delete drive item" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=move-drive-item.php>
                <fieldset>
                    <legend>Moving a OneDrive drive item</legend>
                    <p>Moves a drive item in the OneDrive account using the <code>DriveItem::move</code> method.</p>
                    <div class=form-group>
                        <label for=move-drive-item-id>ID:</label>
                        <input name=id id="move-drive-item-id" placeholder=ID class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=move-drive-item-destination-id>Destination ID:</label>
                        <input name=destination_id id="move-drive-item-destination-id" placeholder="Destination ID" class=form-control>
                    </div>
                    <p><input type=submit value="Move drive item" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=copy-file.php>
                <fieldset>
                    <legend>Copying a OneDrive file</legend>
                    <p>Copies a file in the OneDrive account using the <code>File::copy</code> method. Note that OneDrive does not support copying folders.</p>
                    <div class=form-group>
                        <label for=copy-file-id>ID:</label>
                        <input name=id id="copy-file-id" placeholder=ID class=form-control>
                    </div>
                    <div class=form-group>
                        <label for=copy-file-destination-id>Destination ID:</label>
                        <input name=destination_id id="copy-file-destination-id" placeholder="Destination ID" class=form-control>
                    </div>
                    <p><input type=submit value="Copy file" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=quota.php>
                <fieldset>
                    <legend>Fetching the OneDrive quota</legend>
                    <p>Fetches the OneDrive account's quota using the <code>Client::fetchQuota</code> method.</p>
                    <p><input type=submit value="Fetch quota" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=recent-docs.php>
                <fieldset>
                    <legend>Fetching the OneDrive recent documents</legend>
                    <p>Fetches the recent documents from the OneDrive account using the <code>Client::fetchRecentDocs</code> method.</p>
                    <p><input type=submit value="Fetch recent documents" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=shared.php>
                <fieldset>
                    <legend>Fetching the OneDrive shared drive items</legend>
                    <p>Fetches the drive items shared with the OneDrive account using the <code>Client::fetchShared</code> method.</p>
                    <p><input type=submit value="Fetch shared" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=camera-roll.php>
                <fieldset>
                    <legend>Fetching the OneDrive camera roll</legend>
                    <p>Fetches the drive items shared with the OneDrive account using the <code>Client::fetchShared</code> method.</p>
                    <p><input type=submit value="Fetch camera roll" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=docs.php>
                <fieldset>
                    <legend>Fetching the OneDrive documents</legend>
                    <p>Fetches the drive items shared with the OneDrive account using the <code>Client::fetchShared</code> method.</p>
                    <p><input type=submit value="Fetch documents" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=pics.php>
                <fieldset>
                    <legend>Fetching the OneDrive pictures</legend>
                    <p>Fetches the drive items shared with the OneDrive account using the <code>Client::fetchShared</code> method.</p>
                    <p><input type=submit value="Fetch pictures" class="btn btn-primary"></p>
                </fieldset>
            </form>
            <form action=public-docs.php>
                <fieldset>
                    <legend>Fetching the OneDrive public documents</legend>
                    <p>Fetches the drive items shared with the OneDrive account using the <code>Client::fetchShared</code> method.</p>
                    <p><input type=submit value="Fetch public documents" class="btn btn-primary"></p>
                </fieldset>
            </form>
        </div>
    </body>
</html>

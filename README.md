# GitLab CI and Tag manager

Configuration
-----
1. Copy/Paste the ```.env.dist``` file to ```.env```!
2. Add needed conf in your ```.env``` file!


| Configuration       | description
| ------------------- | ------------------------------------------------------------------ 
| GITLAB_URL          | URL of gitlab server                                                            
| GITLAB_TOKEN        | Token with API access on gitlab
| GITLAB_PROJECTS     | Projects you want! (projectName:projectId separate with a comma 

How to
-----
Here the action implemented at this time:

#### create-tag
Used to create a tag on configure (```.env```) project(s).

Params: 
1. ```action=create-tag``` - Mandatory
2. ```branch=BRANCH_NAME``` - Mandatory
3. ```tag=TAG_NAME``` - Mandatory
4. ```message=YOUR_MESSAGE``` - Non Mandatory

Example:
```
php index.php --action=create-tag --branch=orange/releases/2021.6 --tag=2021.6.0
```

#### check-tag
Used to check if pipelines are successfuly done with a target tag and of course on configure (```.env```) project(s).

Params:
1. ```action=check-tag``` - Mandatory
2. ```tag=TAG_NAME``` - Mandatory

Example:
```
php index.php --action=check-tag --tag=2021.6.0
```

#### create-var
Used to create new CI variable on configure (```.env```) project(s).

Params:
1. ```action=create-var``` - Mandatory
2. ```key=VAR_NAME``` - Mandatory
3. ```value=VAR_VALUE``` - Mandatory
4. ```env=ENV_NAME``` - Mandatory

Example:
```
php index.php --action=create-var --key=CHARTS_VERSION --value=2021.4.0 --env=staging1
```

#### delete-var
Used to delete a CI variable on configure (```.env```) project(s).

Params:
1. ```action=delete-var``` - Mandatory
2. ```key=VAR_NAME``` - Mandatory
4. ```env=ENV_NAME``` - Non Mandatory

Example:
```
php index.php --action=delete-var --key=CHARTS_VERSION --env=staging1
```
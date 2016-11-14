# Blog REST API Assignment

Build a blogging REST API

## Acceptance Tests

In the context of this assignment, the following acceptance tests were established.

* A visitor can retrieve a blog post
* A visitor can retrieve a list of blog posts
* A user can create a blog post
* A new post must pass validation
* A user can update his own blog post
* Updates to posts must past validation
* A user can delete his own blog post
* A user cannot update another user’s post
* A user cannot delete another user’s post
* A user cannot create more than 5 blog posts in the same day
* A user can comment on a blog post
* Subscribers (Author and previous commentors) of a blog post receive email notifications when a new comment is made
* A user becomes popular if a post has comments from 6 or more distinct users

## Setup
Step 1. Clone repository
```
git clone https://github.com/mass6/blog-api.git
```
Step 2. Install package dependancies via composer
```
composer install
```
Step 3. Migrate the database
```
php artisan migrate
```
Step 4. Install Passport Oauth2 Server
```
php artisan passport:install
```
Step 5. Run database seeder.
The seeder will create 10 dummy users for use during testing. User id's will be 1-10.
```
php artisan db:seed
```
Step 6. Generate user API token for desired test user.
```
/reqeust-token/{userId}
```
Note down the 'access_token' key. This is the user token needed to make calls to the API.


## API End Points
Use the access token generated in step 6 above to make calls the below API end points.

**GET /api/posts**

Parameter | Default 
--------- | ------ |
perPage | 20 
page | 1


**GET /api/posts/{id}**

Parameter | Desc 
--------- | ------ |
{id} | PostId


**POST /api/posts**

Parameter | Type | Desc | Rules 
--------- | ------ | --- | ---- |
title | string | Post Title | required: max 255 chars
Body | text | Post Body | required


**PATCH /api/posts/{id}**

Parameter | Type | Desc | Rules 
--------- | ------ | --- | ---- |
{id} | integer | PostId | required
title | string | Post Title | min 1 char, max 255 chars
Body | text | Post Body | min 1 char


**DELETE /api/posts/{id}**

Parameter | Type | Desc | Rules 
--------- | ------ | --- | ---- |
{id} | integer | PostId | required


**POST /api/posts/{id}/comments**

Parameter | Type | Desc | Rules 
--------- | ------ | --- | ---- |
{id} | integer | PostId | required
Body | text | Comment Body | required
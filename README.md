# Spoti5x5 Music Grid

Created by [Pentagear](https://pentagear.github.io)!

## Creating a Spotify Developer App

You'll need to first log into Spotify's [developer app dashboard](https://developer.spotify.com/dashboard). Once there:

1. Click the "Create App" button.
2. Provide an App name, *"Spoti5x5 Music Grid"* for example.
3. Provide an App description, *"Create a 5x5 album art grid of your top 25 artists from the past week."* for example.
4. Provide an App redirect URI, this will need to match the `$redirect_uri` value in the `index.php` file.
5. Tick the "Web API" checkbox when asked **Which API/SDKs are you planning to use**.
6. Agree to Spotify's [Developer Terms of Service](https://developer.spotify.com/terms) and [Design Guidelines](https://developer.spotify.com/documentation/design).

## App Tokens & Script Configuration

Once you've completed creating your Spotify app, it should then be listed in the dashboard.

1. Click your new App in the list.
2. Copy the **Client ID** value. Set the `$client_id` string to this value in the `index.php` file.
3. Click "View Client Secret" to reveal the **Client Secret** token.
4. Copy the **Client Secret** value. Set the `$client_secret` string to this value in the `index.php` file.

## Artists or Tracks?

There are two different options to pick from: artists or tracks. To switch between these options, you can change which URL is used on line `75`, for example to change to a track based listing change it from...

`$ch = curl_init( $top_artists_url );`

...and change it to...

`$ch = curl_init( $top_tracks_url );`

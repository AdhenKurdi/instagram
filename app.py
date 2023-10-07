# Import necessary libraries
import instaloader
from os.path import expanduser
from flask import Flask, jsonify, request, render_template, Response, send_file
from flask_cors import CORS
from textblob import TextBlob
from wordcloud import WordCloud
import matplotlib.pyplot as plt
import networkx as nx
import csv
import json
from io import StringIO

# Create an instance of Instaloader
loader = instaloader.Instaloader()

app = Flask(__name__)
CORS(app, resources={r"/*": {"origins": "*"}})

# Set the template folder path to your desired location
app.template_folder = expanduser("C:/xampp/htdocs/instegrem/")

# Initialize WordCloud outside the route function
wordcloud = WordCloud(width=800, height=400, background_color='white')

@app.route('/')
def index():
    # Render the HTML template for the main page
    return render_template('index.php')

@app.route('/get-network-wordcloud', methods=['GET'])
def get_network_wordcloud():
    try:
        # Get the profile username from the query parameters
        username = request.args.get('username')

        # Retrieve the public profile
        profile = instaloader.Profile.from_username(loader.context, username)

        # Initialize an empty list to store words from captions
        all_words = []

        # Initialize a graph for Network Word Cloud
        G = nx.Graph()

        # Iterate over the profile's posts
        for post in profile.get_posts():
            # Initialize caption for each post
            caption = post.caption
            if caption:
                # Split caption into words
                words = caption.split()
                # Add words to the list of all words
                all_words.extend(words)
                # Add edges to the graph based on word co-occurrence
                for i in range(len(words)):
                    for j in range(i+1, len(words)):
                        word1 = words[i]
                        word2 = words[j]
                        # Add an edge between word1 and word2
                        if not G.has_edge(word1, word2):
                            G.add_edge(word1, word2, weight=1)
                        else:
                            G[word1][word2]['weight'] += 1

        # Generate Network Word Cloud data
        network_wordcloud_data = {
            "nodes": [],
            "links": []
        }

        # Add nodes to the network
        for node in G.nodes:
            network_wordcloud_data["nodes"].append({"id": node, "group": 1})

        # Add links to the network
        for edge in G.edges:
            network_wordcloud_data["links"].append({"source": edge[0], "target": edge[1], "value": G[edge[0]][edge[1]]["weight"]})

        return Response(json.dumps(network_wordcloud_data), mimetype='application/json')

    except Exception as e:
        return jsonify({"error": str(e)}), 500

@app.route('/download-csv', methods=['GET'])
def download_csv():
    try:
        # Get the profile username from the query parameters
        username = request.args.get('username')

        # Retrieve the public profile
        profile = instaloader.Profile.from_username(loader.context, username)

        # Get the follower count for the profile
        follower_count = profile.followers

        # Initialize a list to store post data as dictionaries
        profile_data = []

        # Iterate over the profile's posts
        for post in profile.get_posts():
            post_data = {
                "Date": post.date_local.strftime('%Y-%m-%d'),
                "Caption": post.caption,
                "Likes": post.likes,
                "Comments": post.comments,
                "Engagement": int(post.comments) + int(post.likes),
                "Followers": follower_count,
                "Link": f'https://www.instagram.com/p/{post.shortcode}/',
                "Shortcode": f'https://www.instagram.com/p/{post.shortcode}/',
                "Sentiment": "Netral"  # Inisialisasi sentimen ke "Netral"
            }

            # Analisis sentimen dengan TextBlob
            caption = post.caption
            if caption:
                analysis = TextBlob(caption)
                sentiment = analysis.sentiment
                # Dapatkan nilai sentimen (range dari -1 hingga 1)
                sentiment_value = sentiment.polarity
                # Beri label sentimen berdasarkan nilai sentimen
                if sentiment_value > 0:
                    post_data["Sentiment"] = "Positif"
                elif sentiment_value < 0:
                    post_data["Sentiment"] = "Negatif"
                else:
                    post_data["Sentiment"] = "Netral"

            profile_data.append(post_data)

        # Membuat file CSV dengan data profil
        csv_data = StringIO()
        csv_writer = csv.DictWriter(csv_data, fieldnames=["Date", "Caption", "Likes", "Comments", "Engagement", "Followers", "Link", "Shortcode", "Sentiment"])
        csv_writer.writeheader()
        csv_writer.writerows(profile_data)

        # Menghasilkan response CSV
        response = Response(csv_data.getvalue(), mimetype='text/csv')
        response.headers['Access-Control-Allow-Origin'] = '*'
        response.headers['Content-Disposition'] = f'attachment; filename=profile_data_{username}.csv'
        return response

    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route('/get-instagram-profile', methods=['GET'])
def get_instagram_profile():
    try:
        # Get the profile username and postLimit from the query parameters
        username = request.args.get('username')
        post_limit = request.args.get('postLimit')
        post_limit = int(post_limit) if post_limit else None

        # Retrieve the public profile
        profile = instaloader.Profile.from_username(loader.context, username)

        # Get the follower count for the profile
        follower_count = profile.followers

        # Initialize a list to store post data as dictionaries
        profile_data = []

        # Initialize an empty list to store words from captions
        all_words = []

        # Iterate over the profile's posts
        for post in profile.get_posts():
            # Check if post_limit is reached, if not, continue adding posts
            if post_limit is not None and len(profile_data) >= post_limit:
                break

            # Initialize post_data dictionary for each post
            post_data = {
                "date": post.date_local.strftime('%Y-%m-%d'),
                "caption": post.caption,
                "likes": post.likes,
                "comments": post.comments,
                "engagement": int(post.comments) + int(post.likes),
                "followers": follower_count,
                "link": f'https://www.instagram.com/p/{post.shortcode}/',
                "shortcode": f'https://www.instagram.com/p/{post.shortcode}/',
                "sentimen": "Netral"  # Inisialisasi sentimen ke "Netral"
            }

            # Analisis sentimen dengan TextBlob
            caption = post.caption
            if caption:
                analysis = TextBlob(caption)
                sentiment = analysis.sentiment
                # Dapatkan nilai sentimen (range dari -1 hingga 1)
                sentiment_value = sentiment.polarity
                # Beri label sentimen berdasarkan nilai sentimen
                if sentiment_value > 0:
                    post_data["sentimen"] = "Positif"
                elif sentiment_value < 0:
                    post_data["sentimen"] = "Negatif"
                else:
                    post_data["sentimen"] = "Netral"

            # Tambahkan data postingan ke dalam profile_data
            profile_data.append(post_data)

            # Append words from the caption to the all_words list
            if caption:
                all_words.extend(caption.split())

            # Create a Word Cloud from the collected words
            wordcloud.generate(' '.join(all_words))

            # Save the Word Cloud as an image (optional)
            wordcloud.to_file("static/wordcloud.png")  # Save in a static folder

            # Create a response with CORS headers
            response = jsonify(profile_data)
            response.headers.add('Access-Control-Allow-Origin', '*')

        return response, 200

    except Exception as e:
        return jsonify({"error": str(e)}), 500


if __name__ == '__main__':
    app.run(debug=True)
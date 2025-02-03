// Main orchestrator
export class ContentService {
    processContent(item) {
        console.log('content: ', item);
        const blocks = [];
        const patternGlobal = /(\n+)|(\bhttps?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp)\b)|<.*?src="(https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp))".*?>|(nostr:(?:[a-zA-Z0-9]+))|((?:[^\n])+?)(?=\n|https?:\/\/|$)/gi;
        
        // match[1] - new line
        // match[2] - media
        // match[2] - direct urls
        // match[3] - nsotr identifiers
        // match[4] - plain text

        if (item.event?.content) {
            
            const matches = [...item.event?.content.matchAll(patternGlobal)];
            
            for (const match of matches) {
                console.log('matches: ', matches);
                if (match[1]) {
                    blocks.push({
                        type: 'newline',
                        count: match[1].length
                    });
                }
                if (match[2]?.trim()) {
                    blocks.push({
                        type: this.mediaType(match[2]),
                        // type: 'media',
                        url: match[2].trim()
                    });
                }
                if (match[3]?.trim()) {
                    blocks.push({
                        type: 'embedded',
                        url: match[3].trim()
                    });
                }
                if (match[4]?.trim()) {
                    blocks.push({
                        type: 'nostr',
                        id: match[4].trim()
                    });
                }
                if (match[5]?.trim()) {
                    const content = match[5].trim();
                    const type = this.contentType(content);
           
                    if (type === 'hashtags') {
                        blocks.push({
                            type: 'hashtags',
                            content: this.processHashtags(content)
                        });
                    } else {
                        blocks.push({
                            type: 'text',
                            content: content        
                        });
                    }
           
                }
            }
        }
        // const parts = content.match(pattern);
        console.log('content after processing: ', blocks);
        return blocks;
    }

    mediaType(url) {
        const extension = url.split('.').pop().toLowerCase();
        console.log("extension: ", extension)

        // Check for special platforms
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            return 'youtube';
        }
        if (url.includes('twitter.com') || url.includes('x.com')) {
            return 'twitter';
        }

        const mediaTypes = {
            image: ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            video: ['mp4', 'webm', 'mov', 'ogg'],
            audio: ['mp3', 'wav', 'ogg', 'm4a']
        }
        
        for (const [type, extensions] of Object.entries(mediaTypes)) {
            if (extensions.includes(extension)) {
                return type;
            }
        }

        return 'link';
    }

    contentType(content) {
        const hashtagRegex = /^(?:#[\w]+\s*)+$/;
        if (hashtagRegex.test(content)) {
            return 'hashtags';
        }
        return 'text';
    }
    
    processHashtags(content) {
        const hashtagRegex = /#[\w]+/g;
        const matches = content.match(hashtagRegex);
        return matches || [];
    }
}
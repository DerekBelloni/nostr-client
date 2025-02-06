// Main orchestrator
export class ContentService {
    processContent(item) {
        const blocks = [];
        const patternGlobal = /(\n+)|(\bhttps?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp)\b)|<.*?src="(https?:\/\/[^\s"]+?\.(?:mp4|webm|ogg|mov|jpg|jpeg|png|gif|webp))".*?>|(nostr:(?:[a-zA-Z0-9]+))|((?:[^\n])+?)(?=\n|https?:\/\/|$)/gi;
        
        // match[1] - new line
        // match[2] - media
        // match[3] - direct urls
        // match[4] - nostr identifiers
        // match[5] - plain text/hashtagss

        if (item.event?.content) {
            const matches = [...item.event?.content.matchAll(patternGlobal)];
            
            for (const match of matches) {
                if (match[1]) {
                    blocks.push({
                        type: 'newline',
                        count: match[1].length
                    });
                }
                if (match[2]?.trim()) {
                    blocks.push({
                        type: this.mediaType(match[2]),
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
                    const content = match[4].trim();
                    blocks.push({
                        type: 'nostr',
                        content: this.processNostrEntities(content) 
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
                    } else if (type === 'nostr') {
                        blocks.push({
                            type: 'nostr',
                            content: this.processNostrEntities(content)        
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
        return blocks;
    }

    mediaType(url) {
        const extension = url.split('.').pop().toLowerCase();

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
        const nostrRegex = /nostr:(?:npub|note|nprofile|nevent|naddr|nrelay)[a-zA-Z0-9]{20,}(?![a-zA-Z0-9])/g;

        if (hashtagRegex.test(content)) {
            return 'hashtags';
        }

        if (nostrRegex.test(content)) {
            return 'nostr';
        }

        return 'text';
    }
    
    processHashtags(content) {
        const hashtagRegex = /#[\w]+/g;
        const matches = content.match(hashtagRegex);
        return matches || [];
    }

    processNostrEntities(content) {
        const nostrRegex = /nostr:(?:npub|note|nprofile|nevent|naddr|nrelay)[a-zA-Z0-9]{20,}(?![a-zA-Z0-9])/g;
        const matches = content.match(nostrRegex);

        const structuredEntity = {
            bech32: null,
            identifier: null
        };

        matches.forEach((match) => {
            let parts = match.split(':');
            structuredEntity.bech32 = parts[1];
            structuredEntity.identifier = this.parseIdentifier(parts[1]);
        })
        return structuredEntity || [];
    }

    parseIdentifier(bech32) {
        const match = bech32.match(/^(npub|note|nprofile|nevent|naddr)/);
        return match ? match[1] : null;
    }
}
<?php

namespace TextMod;

class WPCommentsFilter {
    /** @var TextMod  */
    private $textmod;

    /** @var array  */
    private $settings;

    public function __construct(TextMod $textmod, array $settings) {
        $this->settings = $settings;
        $this->textmod = $textmod;

        add_filter('pre_comment_approved', array($this, 'filterComment'), 10, 2);
    }

    public function filterComment($approved, $commentData) {
        if ($approved === '1') {
            // Comment is already approved, skip the spam check
            return $approved;
        }

        if ($this->shouldFilterComment($commentData)) {
            $action = $this->settings['textmod_action'] ?? 'spam';

            if ($action === 'reject') {
                return '0'; // Return '0' to reject the comment
            } elseif ($action === 'spam') {
                wp_spam_comment($commentData['comment_ID']); // Mark the comment as spam
                return 'spam'; // Return 'spam' to indicate the comment is marked as spam
            }
        }

        return $approved;
    }

    public function shouldFilterComment($commentData): bool {
        $moderationResult = $this->textmod->moderate($commentData['comment_content']);

        return $moderationResult->spam ||
            $moderationResult->selfPromoting ||
            $moderationResult->hate ||
            $moderationResult->terrorism ||
            $moderationResult->extremism ||
            $moderationResult->pornographic ||
            $moderationResult->threatening ||
            $moderationResult->selfHarm ||
            $moderationResult->sexual ||
            $moderationResult->sexualMinors ||
            $moderationResult->violence ||
            $moderationResult->violenceGraphic;
    }

    public static function newInstance(): WPCommentsFilter {
        $settings = get_option('textmod_wp_comments_settings');
        $authToken = $settings['textmod_api_key'] ?? '';

        $filterSentiments = [];

        // Add sentiments from settings if checked
        $sentiments = [
            TextMod::SPAM,
            TextMod::SELF_PROMOTING,
            TextMod::HATE,
            TextMod::TERRORISM,
            TextMod::EXTREMISM,
            TextMod::PORNOGRAPHIC,
            TextMod::THREATENING,
            TextMod::SELF_HARM,
            TextMod::SEXUAL,
            TextMod::SEXUAL_MINORS,
            TextMod::VIOLENCE,
            TextMod::VIOLENCE_GRAPHIC,
        ];

        foreach ($sentiments as $sentiment) {
            if (isset($settings[$sentiment]) && $settings[$sentiment] === 'on') {
                $filterSentiments[] = $sentiment;
            }
        }

        $textMod = new TextMod([
            'authToken' => $authToken,
            'filterSentiments' => $filterSentiments,
        ]);

        return new static($textMod, $settings);
    }
}

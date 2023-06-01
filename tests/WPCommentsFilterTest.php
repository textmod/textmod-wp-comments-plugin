<?php

namespace TextMod\Test;

use PHPUnit\Framework\TestCase;
use TextMod\ModerationResult;
use TextMod\TextMod;
use TextMod\WPCommentsFilter;

class WPCommentsFilterTest extends TestCase
{
    private $textmod;
    private $settings;

    protected function setUp()
    {
        $this->textmod = $this->createMock(TextMod::class);
        $this->settings = ['textmod_action' => 'spam'];
    }

    public function filterCommentDataProvider()
    {
        return [
            // Comment is already approved
            [
                'approved' => '1',
                'commentData' => ['comment_ID' =>  '1', 'comment_content' => 'This is a comment'],
                'moderationResult' => new ModerationResult([]),
                'expected' => '1',
            ],
            // Comment should be rejected based on action setting
            [
                'approved' => '0',
                'commentData' =>  ['comment_ID' =>  '1', 'comment_content' => 'This is a spam comment'],
                'moderationResult' => new ModerationResult(['spam' => true]),
                'expected' => '0',
            ],
            // Comment should be marked as spam based on action setting
            [
                'approved' => '0',
                'commentData' =>  ['comment_ID' =>  '1', 'comment_content' => 'This is a spam comment'],
                'moderationResult' => new ModerationResult(['spam' => true]),
                'expected' => 'spam',
            ],
            // Comment should pass through the filter
            [
                'approved' => '0',
                'commentData' =>  ['comment_ID' =>  '1', 'comment_content' => 'This is a normal comment'],
                'moderationResult' => new ModerationResult([]),
                'expected' => '0',
            ],
        ];
    }

    /**
     * @dataProvider filterCommentDataProvider
     */
    public function testFilterComment($approved, $commentData, $moderationResult, $expected)
    {
        wp_set_wpdb_vars();

        if($approved !== "1") {
            $this->textmod->expects($this->once())
                ->method('moderate')
                ->with($this->equalTo($commentData['comment_content']))
                ->willReturn($moderationResult);
        }

        if($expected === '0') {
            $this->settings['textmod_action'] = 'pending';
        }

        $filter = new WPCommentsFilter($this->textmod, $this->settings);
        $result = $filter->filterComment($approved, $commentData);
        $this->assertSame($expected, $result);
    }
}

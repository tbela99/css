<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TBela\CSS\Parser;
use TBela\CSS\Renderer;

final class EscapeString extends TestCase
{
    /**
     * @param string $expected
     * @param string $actual
     * @dataProvider testEscapeStringProvider
     */
    public function testEscapeString($expected, $actual)
    {

        $this->assertEquals(
            $expected,
            $actual
        );
    }

    public function testEscapeStringProvider() {

        $data = [];

        $parser = new Parser('.utf-8:before {

    content: "😀 😃 😄 😁 😆 😅 😂 🤣 🥲 ☺️ 😊 😇 🙂 🙃 😉 😌 😍 🥰 😘 😗 😙 😚 😋 😛 😝 😜 🤪 🤨 🧐 🤓 😎 🥸 🤩 🥳 😏 😒 😞 😔 😟 😕 🙁 ☹️ 😣 😖 😫 😩 🥺 😢 😭 😤 😠 😡 🤬 🤯 😳 🥵 🥶 😱 😨 😰 😥 😓 🤗 🤔 🤭 🤫 🤥 😶 😐 😑 😬 🙄 😯 😦 😧 😮 😲 🥱 😴 🤤 😪 😵 🤐 🥴 🤢 🤮 🤧 😷 🤒 🤕 🤑 🤠 😈 👿 👹 👺 🤡 💩 👻 💀 ☠️ 👽 👾 🤖 🎃 😺 😸 😹 😻 😼 😽 🙀 😿 😾";
', [
            'capture_errors' => true
        ]);

        $data[] = [
            '.utf-8:before {
 content: "\1F600 \1F603 \1F604 \1F601 \1F606 \1F605 \1F602 \1F923 \1F972 \263A\FE0F \1F60A \1F607 \1F642 \1F643 \1F609 \1F60C \1F60D \1F970 \1F618 \1F617 \1F619 \1F61A \1F60B \1F61B \1F61D \1F61C \1F92A \1F928 \1F9D0 \1F913 \1F60E \1F978 \1F929 \1F973 \1F60F \1F612 \1F61E \1F614 \1F61F \1F615 \1F641 \2639\FE0F \1F623 \1F616 \1F62B \1F629 \1F97A \1F622 \1F62D \1F624 \1F620 \1F621 \1F92C \1F92F \1F633 \1F975 \1F976 \1F631 \1F628 \1F630 \1F625 \1F613 \1F917 \1F914 \1F92D \1F92B \1F925 \1F636 \1F610 \1F611 \1F62C \1F644 \1F62F \1F626 \1F627 \1F62E \1F632 \1F971 \1F634 \1F924 \1F62A \1F635 \1F910 \1F974 \1F922 \1F92E \1F927 \1F637 \1F912 \1F915 \1F911 \1F920 \1F608 \1F47F \1F479 \1F47A \1F921 \1F4A9 \1F47B \1F480 \2620\FE0F \1F47D \1F47E \1F916 \1F383 \1F63A \1F638 \1F639 \1F63B \1F63C \1F63D \1F640 \1F63F \1F63E"
}',
            (new Renderer())->renderAst($parser)
        ];

        return $data;
    }
}


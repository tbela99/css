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
    public function testEscapeString($expected, $actual): void
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
 content: "\1f600 \1f603 \1f604 \1f601 \1f606 \1f605 \1f602 \1f923 \1f972 \263a\fe0f \1f60a \1f607 \1f642 \1f643 \1f609 \1f60c \1f60d \1f970 \1f618 \1f617 \1f619 \1f61a \1f60b \1f61b \1f61d \1f61c \1f92a \1f928 \1f9d0 \1f913 \1f60e \1f978 \1f929 \1f973 \1f60f \1f612 \1f61e \1f614 \1f61f \1f615 \1f641 \2639\fe0f \1f623 \1f616 \1f62b \1f629 \1f97a \1f622 \1f62d \1f624 \1f620 \1f621 \1f92c \1f92f \1f633 \1f975 \1f976 \1f631 \1f628 \1f630 \1f625 \1f613 \1f917 \1f914 \1f92d \1f92b \1f925 \1f636 \1f610 \1f611 \1f62c \1f644 \1f62f \1f626 \1f627 \1f62e \1f632 \1f971 \1f634 \1f924 \1f62a \1f635 \1f910 \1f974 \1f922 \1f92e \1f927 \1f637 \1f912 \1f915 \1f911 \1f920 \1f608 \1f47f \1f479 \1f47a \1f921 \1f4a9 \1f47b \1f480 \2620\fe0f \1f47d \1f47e \1f916 \1f383 \1f63a \1f638 \1f639 \1f63b \1f63c \1f63d \1f640 \1f63f \1f63e"
}',
            (new Renderer())->renderAst($parser)
        ];

        return $data;
    }
}


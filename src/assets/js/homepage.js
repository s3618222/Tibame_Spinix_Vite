import { createApp } from "vue";
import Header from "@/components/header.vue";
import Footer from "@/components/footer.vue";
createApp(Header, { solid: true }).mount("#headerApp");
createApp(Footer).mount("#footerApp");

const gsap = window.gsap;

const ScrollTrigger = window.ScrollTrigger; //載入scrollTrigger
gsap.registerPlugin(ScrollTrigger); //啟用

//gsap工具，可將動畫加註執行時的螢幕寬度限制
const mm = gsap.matchMedia();

//抓出主內容01 - 04區塊，套用相同動畫邏輯
const highlightSections = document.querySelectorAll(".highlight");

// 768px以上的畫面才 zoom out 效果
mm.add("(min-width: 769px)", () => {

    const isAtTop = window.scrollY < 50;

    //只有在靠近Hero區域位置刷新載入時，才執行Hero動畫
    if (isAtTop) {
        //建立hero區動畫時間軸 timeline(Tl)
        const heroTl = gsap.timeline();

        heroTl.fromTo(
            ".hero__bg",

            //動畫初始狀態
            { scale: 1.08 },

            //結束狀態
            {
                scale: 1,
                duration: 2.8,
                ease: "power3.out"
            }
        );

        heroTl.from( //只寫from時，動畫的終點會回到CSS原狀態
            ".hero__heading h1",
            {
                y: 40,
                opacity: 0,
                duration: 0.8,
                ease: "power3.out",
            },
            0.6 //代表當動畫時間點播到0.6s時，此動畫就提前進場，不用等到前一動畫執行完
        );

        heroTl.from(
            ".hero__subtitle",
            {
                y: 20,
                opacity: 0,
                duration: 0.6,
                ease: "power3.out"
            },
            0.85
        );

        heroTl.from(
            ".hero__body",
            {
                y: 20,
                opacity: 0,
                duration: 0.6,
                ease: "power3.out"
            },
            1.05
        );

        heroTl.from(
            ".hero__actions .btn-cta",
            {
                y: 16,
                opacity: 0,
                duration: 0.5,
                stagger: 0.1, //當動畫的target一次選到多元素時 (有兩顆CTA按鈕)，stagger可設定元素間的執行間隔要隔多久
                ease: "power3.out"
            },
            1.25
        );
    }

    //Hro捲動離場
    gsap.fromTo(
        ".hero__box",

        { //起點
            y: 0,
            opacity: 1
        },

        { //終點
            y: -80,
            opacity: 0,

            scrollTrigger: {
                trigger: ".hero",
                start: "20% top",
                end: "70% top",
                scrub: 1,

                onLeaveBack: () => {
                    gsap.set(".hero__box", {
                        y: 0,
                        opacity: 1
                    });
                }
            }
        }
    );

    gsap.to(".hero__bg", {
        y: 20, //讓背景圖隨滾動往下跑，與前段文字往上錯開，製造景深視差

        scrollTrigger: {
            trigger: ".hero",
            start: "top top",
            end: "bottom top",
            scrub: 1.2
        }
    });

    // ===== about區動畫 =====
    gsap.from(
        ".about__intro > *",
        {
            y: 24,
            opacity: 0,
            duration: 0.7,
            stagger: 0.12,
            ease: "power3.out",

            scrollTrigger: {
                trigger: ".about",
                start: "top 70%"
            }
        }
    );

    gsap.from(
        ".about__media img",
        {
            y: 30,
            scale: 0.92,
            rotation: -40,
            opacity: 0,
            duration: 1,
            ease: "power3.out",

            scrollTrigger: {
                trigger: ".about",
                start: "top 70%"
            },

            //等前面動畫完整播放後，接續執行
            onComplete: () => {
                gsap.to(".about__media img", {
                    y: -12,
                    duration: 3.5,
                    repeat: -1, //無限重複
                    yoyo: true, //當動畫跑至終點後，會在反向播放回至原點
                    ease: "sine.inOut"
                });
            }
        }
    );

    gsap.from(
        ".feature-card",
        {
            y: 24,
            opacity: 0,
            duration: 0.6,
            stagger: 0.12,
            ease: "power3.out",

            scrollTrigger: {
                trigger: ".about__features",
                start: "top 75%"
            }
        }
    );

    // ===== Highlight 01~04 共用動畫 =====
    highlightSections.forEach((section) => {
        const media = section.querySelector(".highlight__media");
        const image = section.querySelector(".highlight__media img");
        const textItems = section.querySelectorAll(".highlight__text > *");
        const number = section.querySelector(".highlight__number");

        //判斷內容是否為02、04區
        const isReverse = section.classList.contains("highlight--reverse");
        const isBuild = section.id === "build";

        // 01、03、04才使用一般圖片動畫
        if (!isBuild) {
            gsap.fromTo(
                media,
                {
                    //01、03區從右側向左裁掉100%圖片
                    //02、04區相反
                    clipPath: isReverse ? "inset(0% 0% 0% 100%)" : "inset(0% 100% 0% 0%)"
                },

                {
                    //再讓圖片完整顯示，產生從左、或右邊慢慢掀開圖片效果
                    clipPath: "inset(0% 0% 0% 0%)",
                    duration: 1.1,
                    ease: "power3.inOut",

                    scrollTrigger: {
                        trigger: section,
                        start: "top 70%"
                    }
                }
            );

            gsap.fromTo(
                image,
                {
                    scale: 1.08
                },
                {
                    scale: 1,
                    duration: 1.3,
                    ease: "power3.out",

                    scrollTrigger: {
                        trigger: section,
                        start: "top 70%"
                    }
                }
            );
        }

        gsap.from(
            textItems,
            {
                y: 28,
                opacity: 0,
                duration: 0.7,
                stagger: 0.12,
                ease: "power3.out",

                scrollTrigger: {
                    trigger: section,
                    start: "top 65%"
                }
            }
        );

        gsap.fromTo(
            number,
            {
                y: 30
            },

            {
                y: -30,

                scrollTrigger: {
                    trigger: section,
                    start: "top bottom",
                    end: "bottom top",
                    scrub: 1.2
                }
            }
        );
    });
});

// 02區圖片獨立動畫
mm.add("(min-width: 769px)", () => {

    const buildSection = document.querySelector("#build");

    const blade = buildSection.querySelector(".build-part--blade");
    const lock = buildSection.querySelector(".build-part--lock");
    const core = buildSection.querySelector(".build-part--core");

    const buildTl = gsap.timeline({
        scrollTrigger: {
            trigger: buildSection,
            start: "top 40%",
            end: "center 40%",
            scrub: 1
        }
    });

    buildTl.fromTo(
        blade,
        {
            y: -140,
            rotation: -8
        },
        {
            y: 0,
            rotation: 0,
            ease: "none"
        },
        0
    );

    buildTl.fromTo(
        lock,
        {
            y: -45,
            rotation: 3
        },
        {
            y: 0,
            rotation: 0,
            ease: "none"
        },
        0
    );

    buildTl.fromTo(
        core,
        {
            y: 100,
            rotation: 7
        },
        {
            y: 0,
            rotation: 0,
            ease: "none"
        },
        0
    );

});

// ===== FEEL THE SPIRIT 背景動畫 =====
gsap.fromTo(
    ".video-cta__bg",
    {
        scale: 1.08
    },
    {
        scale: 1,
        duration: 1.8,
        ease: "power3.out",

        scrollTrigger: {
            trigger: ".video-cta",
            start: "top 70%"
        }
    }
);
gsap.from(
    ".video-cta__box",
    {
        y: 40,
        opacity: 0,
        duration: 0.9,
        ease: "power3.out",

        scrollTrigger: {
            trigger: ".video-cta",
            start: "top 65%"
        }
    }
);

// 768px以下時的Hero動畫版本; 只留文字進場效果
mm.add("(max-width: 768px)", () => {
    const mobileTl = gsap.timeline();

    mobileTl.from(
        ".hero__heading h1",
        {
            y: 24,
            opacity: 0,
            duration: 0.65,
            ease: "power3.out"
        }
    );

    mobileTl.from(
        ".hero__subtitle",
        {
            y: 16,
            opacity: 0,
            duration: 0.5,
            ease: "power3.out"
        },
        "-=0.3" //代表比前一個動畫的結束時間，提早0.3s執行
    );

    mobileTl.from(
        ".hero__body",
        {
            y: 16,
            opacity: 0,
            duration: 0.5,
            ease: "power3.out"
        },
        "-=0.28"
    );

    mobileTl.from(
        ".hero__actions .btn-cta",
        {
            y: 12,
            opacity: 0,
            duration: 0.45,
            stagger: 0.16,
            ease: "power3.out"
        },
        "-=0.25"
    );
});


// 頁面載入後，重新計算 ScrollTrigger 位置
window.addEventListener("load", () => {
    ScrollTrigger.refresh();
});
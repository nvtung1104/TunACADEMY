import DefaultTheme from 'vitepress/theme'
import GuidesHome from './GuidesHome.vue'
import LevelCards from './LevelCards.vue'
import RoadmapPage from './RoadmapPage.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }: { app: any }) {
    app.component('GuidesHome', GuidesHome)
    app.component('LevelCards', LevelCards)
    app.component('RoadmapPage', RoadmapPage)
  }
}

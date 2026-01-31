import * as echarts from 'echarts/core'
import { CanvasRenderer } from 'echarts/renderers'
import { LineChart as ELineChart } from 'echarts/charts'
import { BarChart as EBarChart } from 'echarts/charts'
import { PieChart as EPieChart } from 'echarts/charts'
import { RadarChart as ERadarChart } from 'echarts/charts'
import { GridComponent, LegendComponent, TitleComponent, TooltipComponent, ToolboxComponent } from 'echarts/components'

// Register common components and charts once for the whole app.
// This prevents duplicate registration across multiple components and centralizes maintenance.
echarts.use([
  CanvasRenderer,
  ELineChart,
  EBarChart,
  EPieChart,
  ERadarChart,
  GridComponent,
  LegendComponent,
  TitleComponent,
  TooltipComponent,
  ToolboxComponent,
])

export default echarts

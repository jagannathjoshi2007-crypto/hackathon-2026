import Card from '../components/Card'

const Dashboard = () => {
  return (
    <div style={{ display: 'grid', gap: '1rem' }}>
      <Card title="Overview">
        <p>Welcome to the dashboard.</p>
      </Card>
      <Card title="Recent Activity">
        <p>No recent activity yet.</p>
      </Card>
    </div>
  )
}

export default Dashboard

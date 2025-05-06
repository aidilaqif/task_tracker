import 'package:flutter/material.dart';
import 'package:flutter_mobile_app/app_theme.dart';
import 'package:flutter_mobile_app/services/api_services.dart';
import 'package:flutter_mobile_app/widgets/member_card.dart';

class TeamPage extends StatefulWidget {
  final Map<String, dynamic> userData;
  const TeamPage({super.key, required this.userData});

  @override
  State<TeamPage> createState() => _TeamPageState();
}

class _TeamPageState extends State<TeamPage> {
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  String _errorMessage = '';
  Map<String, dynamic> _teamInfo = {};
  List<dynamic> _teamMembers = [];
  
  @override
  void initState() {
    super.initState();
    _loadTeamData();
  }

  Future<void> _loadTeamData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      // Get user's team ID from userData
      final teamIdRaw = widget.userData['user']['team_id'];
      
      if (teamIdRaw == null) {
        setState(() {
          _isLoading = false;
          _errorMessage = 'You are not assigned to any team';
        });
        return;
      }

      // Convert team Id to integer
      final teamId = teamIdRaw is String? int.parse(teamIdRaw) : teamIdRaw;

      // Fetch team info
      final teamResponse = await _apiService.getTeamInfo(teamId);
      
      if (teamResponse['status']) {
        setState(() {
          _teamInfo = teamResponse['data'] ?? {};
        });
        
        // Fetch team members
        final membersResponse = await _apiService.getTeamMembers(teamId);
        
        if (membersResponse['status']) {
          setState(() {
            _teamMembers = membersResponse['data']['members'] ?? [];
            _isLoading = false;
          });
        } else {
          setState(() {
            _errorMessage = membersResponse['msg'] ?? 'Failed to load team members';
            _isLoading = false;
          });
        }
      } else {
        setState(() {
          _errorMessage = teamResponse['msg'] ?? 'Failed to load team information';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Error loading team data: ${e.toString()}';
        _isLoading = false;
      });
    }
  }
  
  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _loadTeamData,
      child: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _errorMessage.isNotEmpty
              ? _buildErrorView()
              : _buildTeamView(),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.error_outline,
              size: 70,
              color: AppTheme.errorColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'Team Information Unavailable',
              style: AppTheme.titleStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              _errorMessage,
              style: AppTheme.bodyStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingLg),
            ElevatedButton(
              onPressed: _loadTeamData,
              child: Text('Try Again'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTeamView() {
    return SingleChildScrollView(
      physics: const AlwaysScrollableScrollPhysics(),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Team Header
            _TeamHeader(
              name: _teamInfo['name'] ?? 'Team',
              description: _teamInfo['description'] ?? 'No description available',
            ),
            SizedBox(height: AppTheme.spacingLg),
            
            // Team Members Section
            Text(
              'Team Members',
              style: AppTheme.titleStyle,
            ),
            SizedBox(height: AppTheme.spacingMd),
            
            // Team Members List
            _teamMembers.isEmpty
                ? _EmptyMembersView()
                : ListView.builder(
                    physics: NeverScrollableScrollPhysics(),
                    shrinkWrap: true,
                    itemCount: _teamMembers.length,
                    itemBuilder: (context, index) {
                      final member = _teamMembers[index];
                      return MemberCard(
                        name: member['name'] ?? 'Unknown',
                        email: member['email'] ?? '',
                        role: member['role'] ?? 'Member',
                        isCurrentUser: member['id'].toString() == 
                            widget.userData['user']['id'].toString(),
                      );
                    },
                  ),
          ],
        ),
      ),
    );
  }
}

class _TeamHeader extends StatelessWidget {
  final String name;
  final String description;

  const _TeamHeader({
    required this.name,
    required this.description,
  });

  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: AppTheme.elevationSm,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
      ),
      child: Padding(
        padding: EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Team Icon
            Row(
              children: [
                CircleAvatar(
                  radius: 30,
                  backgroundColor: AppTheme.primaryColor.withOpacity(0.1),
                  child: Icon(
                    Icons.people,
                    size: 30,
                    color: AppTheme.primaryColor,
                  ),
                ),
                SizedBox(width: AppTheme.spacingMd),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        name,
                        style: AppTheme.headlineStyle,
                        overflow: TextOverflow.ellipsis,
                      ),
                      SizedBox(height: AppTheme.spacingXs),
                      Text(
                        'Team',
                        style: AppTheme.captionStyle.copyWith(
                          color: AppTheme.textSecondaryColor,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            SizedBox(height: AppTheme.spacingMd),
            // Divider between team header and description
            Divider(height: 1, thickness: 1),
            SizedBox(height: AppTheme.spacingMd),
            // Team Description
            Text(
              'Description',
              style: AppTheme.subtitleStyle,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              description,
              style: AppTheme.bodyStyle,
            ),
          ],
        ),
      ),
    );
  }
}

class _EmptyMembersView extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Card(
      elevation: AppTheme.elevationSm,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(AppTheme.borderRadiusMd),
      ),
      child: Padding(
        padding: EdgeInsets.all(AppTheme.spacingLg),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.people_outline,
              size: 60,
              color: AppTheme.textSecondaryColor,
            ),
            SizedBox(height: AppTheme.spacingMd),
            Text(
              'No Team Members',
              style: AppTheme.subtitleStyle,
              textAlign: TextAlign.center,
            ),
            SizedBox(height: AppTheme.spacingSm),
            Text(
              'This team doesn\'t have any members yet',
              style: AppTheme.bodyStyle.copyWith(
                color: AppTheme.textSecondaryColor,
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}